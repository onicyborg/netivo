<?php

namespace App\Enums;

enum ReportStatus: string
{
    case DIKIRIM = 'dikirim';
    case REVISI = 'revisi';
    case DIARSIPKAN = 'diarsipkan';
}
