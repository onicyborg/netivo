<?php

namespace App\Enums;

enum BillStatus: string
{
    case BELUM_BAYAR = 'belum_bayar';
    case MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    case LUNAS = 'lunas';
    case TERLAMBAT = 'terlambat';
}
