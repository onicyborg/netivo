<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\DailyReport;
use App\Models\Payment;
use App\Models\User;
use App\Services\DailyReportGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_summary_uses_verified_date_and_counts_pending_by_paid_date(): void
    {
        $date = '2026-09-24';
        $this->payment(PaymentStatus::CONFIRMED, 100000, ['verified_at' => $date.' 08:00:00']);
        $this->payment(PaymentStatus::CONFIRMED, 200000, ['verified_at' => $date.' 09:00:00']);
        $this->payment(PaymentStatus::REJECTED, 50000, ['verified_at' => $date.' 10:00:00']);
        $this->payment(PaymentStatus::PENDING, 75000, ['paid_date' => $date]);
        $this->payment(PaymentStatus::CONFIRMED, 999000, ['verified_at' => '2026-09-23 23:59:00']);

        $result = app(DailyReportGenerator::class)->generate($date);
        $report = $result['report'];

        $this->assertTrue($result['created']);
        $this->assertSame(2, $report->total_confirmed_count);
        $this->assertSame('300000.00', (string) $report->total_confirmed_amount);
        $this->assertSame(1, $report->rejected_count);
        $this->assertSame(1, $report->pending_count);
        $this->assertCount(4, app(DailyReportGenerator::class)->paymentsForDate($date));
    }

    public function test_manual_report_is_unique_and_cron_does_not_overwrite_it(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $generator = app(DailyReportGenerator::class);
        $first = $generator->generate('2026-09-24', 'manual', $admin);
        $second = $generator->generate('2026-09-24', 'cron');

        $this->assertTrue($first['created']);
        $this->assertTrue($second['skipped']);
        $this->assertSame('manual', $second['report']->source);
        $this->assertSame(1, DailyReport::whereDate('report_date', '2026-09-24')->count());
    }

    public function test_full_report_review_flow_and_archived_report_is_read_only(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

        $this->actingAs($admin)->post(route('admin.reports.store'), ['report_date' => '2026-09-24'])->assertRedirect();
        $report = DailyReport::firstOrFail();
        $this->assertSame(ReportStatus::DIKIRIM, $report->status);
        $this->assertDatabaseHas('notifications', ['user_id' => $supervisor->id, 'type' => 'report_sent']);
        $this->assertDatabaseHas('system_logs', ['table_name' => 'daily_reports', 'record_id' => $report->id, 'action' => 'generate_manual']);

        $this->actingAs($supervisor)->post(route('supervisor.reports.revision', $report), [])->assertSessionHasErrors('revision_note');
        $this->actingAs($supervisor)->post(route('supervisor.reports.revision', $report), ['revision_note' => 'Tambahkan transaksi sore.'])->assertRedirect();
        $this->assertSame(ReportStatus::REVISI, $report->fresh()->status);
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'type' => 'report_revision']);
        $this->assertDatabaseHas('system_logs', ['table_name' => 'daily_reports', 'record_id' => $report->id, 'action' => 'revise']);

        $this->actingAs($admin)->post(route('admin.reports.resend', $report))->assertRedirect();
        $this->assertSame(ReportStatus::DIKIRIM, $report->fresh()->status);
        $this->assertSame('Tambahkan transaksi sore.', $report->fresh()->revision_note);

        $this->actingAs($supervisor)->post(route('supervisor.reports.archive', $report))->assertRedirect();
        $this->assertSame(ReportStatus::DIARSIPKAN, $report->fresh()->status);
        $this->assertNotNull($report->fresh()->archived_at);
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'type' => 'report_archived']);
        $this->assertDatabaseHas('system_logs', ['table_name' => 'daily_reports', 'record_id' => $report->id, 'action' => 'archive']);
        $this->actingAs($admin)->post(route('admin.reports.resend', $report))->assertForbidden();
        $this->actingAs($supervisor)->post(route('supervisor.reports.archive', $report))->assertForbidden();
    }

    public function test_report_actions_are_restricted_to_the_correct_roles(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
        $report = DailyReport::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($supervisor)->post(route('admin.reports.store'), ['report_date' => '2026-09-25'])->assertForbidden();
        $this->actingAs($admin)->post(route('supervisor.reports.archive', $report))->assertForbidden();
    }

    public function test_daily_report_cron_is_idempotent_and_logged(): void
    {
        config(['services.cron.secret' => 'report-secret']);
        $headers = ['Authorization' => 'Bearer report-secret'];

        $this->withHeaders($headers)->postJson(route('cron.daily-report'))->assertOk()->assertJsonPath('data.created', 1);
        $this->withHeaders($headers)->postJson(route('cron.daily-report'))->assertOk()->assertJsonPath('data.skipped', 1);
        $this->assertDatabaseCount('daily_reports', 1);
        $this->assertDatabaseCount('cron_logs', 2);
    }

    private function payment(PaymentStatus $status, int $amount, array $overrides = []): Payment
    {
        return Payment::factory()->create(array_merge([
            'status' => $status,
            'amount' => $amount,
            'paid_date' => '2026-09-24',
            'verified_at' => null,
        ], $overrides));
    }
}
