<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Appointment;
use App\Models\EmployeeWorkHour;
use App\Models\TenantBusinessHour;
use App\Models\TenantHoliday;
use App\Models\TenantTimeBlock;

final class ScheduleValidator
{
    /**
     * @return array{ok:bool, error:?string}
     */
    public static function validate(int $tenantId, int $employeeId, string $startsAt, string $endsAt): array
    {
        if (strtotime($startsAt) === false || strtotime($endsAt) === false) {
            return ['ok' => false, 'error' => 'Data/hora inválida'];
        }

        if (strtotime($endsAt) <= strtotime($startsAt)) {
            return ['ok' => false, 'error' => 'Horário final deve ser maior que o inicial'];
        }

        $day = date('Y-m-d', strtotime($startsAt));
        $weekday = (int)date('w', strtotime($startsAt));

        if (TenantHoliday::isClosedDay($tenantId, $day)) {
            return ['ok' => false, 'error' => 'Empresa não atende neste dia (feriado/fechado)'];
        }

        $bh = TenantBusinessHour::findForWeekday($tenantId, $weekday);
        if ($bh === null || (int)$bh['active'] !== 1) {
            return ['ok' => false, 'error' => 'Empresa fechada neste dia'];
        }

        $startTime = date('H:i:s', strtotime($startsAt));
        $endTime = date('H:i:s', strtotime($endsAt));
        $openTime = (string)$bh['open_time'];
        $closeTime = (string)$bh['close_time'];

        if ($startTime < $openTime || $endTime > $closeTime) {
            return ['ok' => false, 'error' => 'Fora do horário de funcionamento'];
        }

        if (EmployeeWorkHour::hasAnyForEmployee($tenantId, $employeeId)) {
            $ewh = EmployeeWorkHour::findForEmployeeWeekday($tenantId, $employeeId, $weekday);
            if ($ewh === null || (int)$ewh['active'] !== 1) {
                return ['ok' => false, 'error' => 'Profissional indisponível neste dia'];
            }

            $wStart = (string)$ewh['start_time'];
            $wEnd = (string)$ewh['end_time'];
            if ($startTime < $wStart || $endTime > $wEnd) {
                return ['ok' => false, 'error' => 'Fora do horário do profissional'];
            }
        }

        if (TenantTimeBlock::hasOverlap($tenantId, $employeeId, $startsAt, $endsAt)) {
            return ['ok' => false, 'error' => 'Horário bloqueado'];
        }

        if (Appointment::hasOverlapForEmployee($tenantId, $employeeId, $startsAt, $endsAt)) {
            return ['ok' => false, 'error' => 'Conflito com outro agendamento'];
        }

        return ['ok' => true, 'error' => null];
    }
}
