<?php

namespace App\Http\Controllers;

use App\Models\TrainingPackage;
use App\Models\TrainingRegistration;
use App\Models\User;
use App\Notifications\AdminTrainingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class TrainingController extends Controller
{
    public function index()
    {
        $packages = TrainingPackage::where('is_active', true)->get();

        return response()->json([
            'message' => 'Daftar paket pelatihan',
            'data' => $packages,
        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'training_package_id' => 'required|exists:training_packages,id',
            'is_custom_schedule' => 'required|boolean',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'participant_count' => 'required|integer|min:1',
            'scheduled_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:1000',
            'user_notes' => 'nullable|string',
        ]);

        $isCustom = filter_var($request->is_custom_schedule, FILTER_VALIDATE_BOOLEAN);

        if (!$isCustom) {
            $dayOfWeek = date('N', strtotime($request->scheduled_date));

            if ($dayOfWeek != 3) {
                return response()->json([
                    'message' => 'Pelatihan reguler hanya tersedia di hari Rabu.',
                ], 422);
            }
        }

        if ($isCustom) {
            if (!$request->scheduled_time) {
                return response()->json([
                    'message' => 'Jam pelatihan custom wajib diisi.',
                ], 422);
            }

            if (!$request->location) {
                return response()->json([
                    'message' => 'Lokasi pelatihan custom wajib diisi.',
                ], 422);
            }
        }

        $package = TrainingPackage::findOrFail($request->training_package_id);
        $totalPrice = $package->price * $request->participant_count;

        $scheduledTime = $isCustom ? $request->scheduled_time : '09:00';
        $location = $isCustom ? $request->location : 'Galeri Sabira Ecoprint';

        $registration = TrainingRegistration::create([
            'user_id' => $request->user()->id,
            'training_package_id' => $request->training_package_id,
            'is_custom_schedule' => $isCustom,
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $scheduledTime,
            'location' => $location,
            'participant_count' => $request->participant_count,
            'user_notes' => $request->user_notes,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        // KONFIGURASI MIDTRANS SIMULATOR - JANGAN DIUBAH
        // KONFIGURASI MIDTRANS
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => 'TRX-TRAIN-' . $registration->id,
                'gross_amount' => $registration->total_price,
            ],
            'customer_details' => [
                'first_name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            Log::error('MIDTRANS TRAINING ERROR', [
                'message' => $e->getMessage(),
                'order_id' => $params['transaction_details']['order_id'],
            ]);

            return response()->json([
                'message' => 'Gagal membuat transaksi Midtrans',
                'error' => $e->getMessage(),
            ], 500);
        }

        $registration->snap_token = $snapToken;
        $registration->save();

        // NOTIFIKASI ADMIN FILAMENT UNTUK TRAINING
        try {
            $admin = User::find(1);

            if ($admin) {
                $registration->load(['user', 'trainingPackage']);
                $admin->notify(new AdminTrainingNotification($registration));
            }
        } catch (\Throwable $notificationError) {
            Log::error('Gagal membuat notifikasi training admin', [
                'training_registration_id' => $registration->id,
                'message' => $notificationError->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Pendaftaran berhasil',
            'data' => [
                'id' => $registration->id,
                'training_package_id' => $registration->training_package_id,
                'is_custom_schedule' => $registration->is_custom_schedule,
                'scheduled_date' => $registration->scheduled_date,
                'scheduled_time' => $registration->scheduled_time,
                'location' => $registration->location,
                'participant_count' => $registration->participant_count,
                'user_notes' => $registration->user_notes,
                'total_price' => $registration->total_price,
                'status' => $registration->status,
                'snap_token' => $snapToken,
            ],
        ], 201);
    }

    public function myTrainings(Request $request)
    {
        $accessStatuses = [
            'approved_by_admin',
            'paid',
            'completed',
        ];

        $registrations = TrainingRegistration::with('trainingPackage')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function ($registration) use ($accessStatuses) {
                $package = $registration->trainingPackage;
                $canAccess = in_array($registration->status, $accessStatuses);

                return [
                    'id' => $registration->id,
                    'training_package_id' => $registration->training_package_id,
                    'is_custom_schedule' => $registration->is_custom_schedule,
                    'scheduled_date' => $registration->scheduled_date,
                    'scheduled_time' => $registration->scheduled_time,
                    'location' => $registration->location,
                    'participant_count' => $registration->participant_count,
                    'user_notes' => $registration->user_notes,
                    'total_price' => $registration->total_price,
                    'status' => $registration->status,

                    'trainingPackage' => [
                        'id' => $package?->id,
                        'title' => $package?->title,
                        'description' => $package?->description,
                        'type' => $package?->type,
                        'delivery_mode' => $package?->delivery_mode ?? 'offline',
                        'group_link' => $canAccess ? $package?->group_link : null,
                        'meeting_link' => $canAccess ? $package?->meeting_link : null,
                        'material_link' => $canAccess ? $package?->material_link : null,
                        'after_acceptance_note' => $canAccess ? $package?->after_acceptance_note : null,
                    ],
                ];
            });

        return response()->json([
            'message' => 'Berhasil mengambil data pelatihan pengguna',
            'data' => $registrations,
        ], 200);
    }
}