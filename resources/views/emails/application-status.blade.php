<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembaruan Status Lamaran</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Manrope', 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.7;
            color: #1f2933;
            background-color: #f5f7fa;
            padding: 20px;
        }

        .email-container {
            max-width: 640px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(17, 24, 39, 0.12);
            border: 1px solid #e5e7eb;
        }

        .header {
            background: linear-gradient(135deg, #0ea5e9 0%, #f59e0b 100%);
            color: #ffffff;
            padding: 40px 32px;
            text-align: center;
        }

        .header h1 {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.2px;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.95;
        }

        .content {
            padding: 36px 32px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 18px;
            color: #0f172a;
        }

        .message {
            font-size: 15px;
            color: #334155;
            margin-bottom: 18px;
            line-height: 1.8;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 14px;
            margin: 24px 0 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-hired {
            background-color: #0ea5e9;
            color: #ffffff;
        }

        .status-rejected {
            background-color: #ef4444;
            color: #ffffff;
        }

        .status-progress {
            background-color: #f59e0b;
            color: #0f172a;
        }

        .job-details {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0ea5e9;
            padding: 20px;
            margin: 24px 0;
            border-radius: 8px;
        }

        .job-details h3 {
            font-size: 15px;
            color: #1f2937;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .job-details p {
            font-size: 14px;
            color: #475569;
            margin: 6px 0;
        }

        .job-details strong {
            color: #0f172a;
        }

        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 28px 0;
        }

        .footer {
            background-color: #f8fafc;
            padding: 28px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            font-size: 13px;
            color: #6b7280;
            margin: 6px 0;
        }

        .emoji {
            font-size: 20px;
        }

        @media only screen and (max-width: 640px) {
            body {
                padding: 0;
            }

            .email-container {
                border-radius: 0;
            }

            .header,
            .content,
            .footer {
                padding: 26px 20px;
            }

            .header h1 {
                font-size: 22px;
            }

            .status-badge {
                font-size: 13px;
                padding: 10px 16px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>Pembaruan Status Lamaran</h1>
            <p>{{ config('app.name') }} · Sistem Rekrutmen</p>
        </div>

        <div class="content">
            <div class="greeting">
                Halo {{ $userName }},
            </div>

            @if ($isHired)
                <p class="message">
                    Selamat! Lamaran Anda untuk posisi <strong>{{ $vacancyTitle }}</strong> dinyatakan
                    <strong>diterima</strong>.
                </p>

                <div class="status-badge status-hired">
                    <span class="emoji">🎉</span> Diterima
                </div>

                <p class="message">
                    Tim kami sangat terkesan dengan pengalaman dan potensi yang Anda tunjukkan sepanjang proses seleksi.
                    Tim HR akan segera menghubungi Anda untuk jadwal onboarding dan informasi penting lainnya.
                </p>

                <div class="job-details">
                    <h3>Detail Posisi</h3>
                    <p><strong>Posisi:</strong> {{ $vacancyTitle }}</p>
                    <p><strong>Lokasi:</strong> {{ $vacancyLocation }}</p>
                </div>

                <p class="message">
                    Kami tidak sabar menantikan kehadiran Anda di tim.
                </p>
            @elseif($isRejected)
                <p class="message">
                    Terima kasih telah melamar posisi <strong>{{ $vacancyTitle }}</strong> dan meluangkan waktu
                    mengikuti proses seleksi.
                </p>

                <div class="status-badge status-rejected">
                    Tidak Lolos Seleksi
                </div>

                <p class="message">
                    Setelah melalui pertimbangan yang matang, kami belum dapat melanjutkan lamaran Anda saat ini.
                    Kami menghargai minat dan usaha Anda, dan berharap Anda tetap mempertimbangkan peluang lain di masa
                    depan.
                </p>

                <div class="job-details">
                    <h3>Posisi yang Dilamar</h3>
                    <p><strong>Posisi:</strong> {{ $vacancyTitle }}</p>
                    <p><strong>Lokasi:</strong> {{ $vacancyLocation }}</p>
                </div>

                <p class="message">
                    Semoga sukses untuk langkah karier Anda berikutnya.
                </p>
            @else
                <p class="message">
                    Status lamaran Anda untuk posisi <strong>{{ $vacancyTitle }}</strong> telah diperbarui.
                </p>

                <div class="status-badge status-progress">
                    Status: {{ $statusLabel }}
                </div>

                <div class="job-details">
                    <h3>Detail Lamaran</h3>
                    <p><strong>Posisi:</strong> {{ $vacancyTitle }}</p>
                    <p><strong>Lokasi:</strong> {{ $vacancyLocation }}</p>
                    <p><strong>Status Saat Ini:</strong> {{ $statusLabel }}</p>
                </div>

                <p class="message">
                    Kami akan terus mengabari perkembangan lamaran Anda. Terima kasih atas kesabarannya.
                </p>
            @endif

            <hr class="divider">

            <p class="message" style="font-size: 14px;">
                Jika ada pertanyaan, silakan hubungi tim HR kami. Email ini dikirim otomatis, mohon tidak membalas
                langsung.
            </p>
        </div>

        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>&copy; {{ date('Y') }} Hak cipta dilindungi.</p>
        </div>
    </div>
</body>

</html>
