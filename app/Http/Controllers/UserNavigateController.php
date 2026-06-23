<?php

namespace App\Http\Controllers;

use App\Models\qrCode;
use App\Services\CalendarService;
use App\Services\HolidayService;
use App\Services\Settings\UserSettingsService;
use App\Services\UploadImageService;
use Illuminate\Support\Facades\Auth;

class UserNavigateController extends Controller
{
    public function __construct(
        private readonly UserSettingsService $userSettingsService,
    ) {}

    public function toUploadImgLaporan(UploadImageService $service)
    {
        $qrId = qrCode::whereId(request('id'))->first()->data ?? null;
        $separatedQrId = explode('-', $qrId);

        if (in_array(Auth::user()->jabatan_id, ['10', '12', '13', '19', '20'])) {
            if (request()->has('id')) {
                return redirect()->route('finding.index', [
                    'n' => $separatedQrId[0],
                    'temuan' => request('temuan'),
                ]);
            } else {
                return redirect()->route('finding.index', [
                    'n' => request('n'),
                    'temuan' => request('temuan'),
                ]);
            }
        }

        if(request('id')) {
            $data = $service->getUploadImageData($separatedQrId);
        } else {
            $data = $service->getUploadImageData();
        }

        return view('pages.user.send_img.create', $data, $separatedQrId);
    }

    public function toCalenderUpload(HolidayService $holidayService, CalendarService $calendarService)
    {
        $holidays = $holidayService->getHolidays();
        $data = $calendarService->getCalendarData();

        return view('pages.user.calender.index', [
            'holidays' => $holidays,
            'translate' => $data['translate'],
            'uploadsByDay' => $data['uploadsByDay'],
        ]);
    }

    public function toSettings()
    {
        $dataSetting = $this->userSettingsService->getByUser((int) auth()->id());
        $preferences = array_merge(
            [
                'theme_mode' => 'light',
                'splash_on_login' => false,
            ],
            is_array($dataSetting?->data_theme) ? $dataSetting->data_theme : []
        );

        if (($preferences['theme_mode'] ?? null) === 'silk') {
            $preferences['theme_mode'] = 'light';
        }

        return view('pages.user.settings.index', compact('preferences'));
    }
}
