<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status Update</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1a202c;
        }

        .message {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .status-badge {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            margin: 25px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-hired {
            background-color: #10b981;
            color: #ffffff;
        }

        .status-rejected {
            background-color: #ef4444;
            color: #ffffff;
        }

        .job-details {
            background-color: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
        }

        .job-details h3 {
            font-size: 16px;
            color: #2d3748;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .job-details p {
            font-size: 14px;
            color: #4a5568;
            margin: 5px 0;
        }

        .job-details strong {
            color: #1a202c;
        }

        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 30px 0;
        }

        .footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            font-size: 14px;
            color: #718096;
            margin: 5px 0;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .emoji {
            font-size: 24px;
            margin-right: 8px;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }

            .header,
            .content,
            .footer {
                padding: 25px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .status-badge {
                font-size: 14px;
                padding: 10px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>Application Status Update</h1>
            <p>{{ config('app.name') }} - Recruitment System</p>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="greeting">
                Dear {{ $userName }},
            </div>

            @if ($isHired)
                <p class="message">
                    We are <strong>thrilled</strong> to inform you that your application for the position of
                    <strong>{{ $vacancyTitle }}</strong> has been <strong>successful</strong>!
                </p>

                <div class="status-badge status-hired">
                    <span class="emoji">🎉</span> HIRED
                </div>

                <p class="message">
                    Congratulations! We were highly impressed by your qualifications, experience, and
                    the potential you demonstrated throughout the recruitment process.
                </p>

                <div class="job-details">
                    <h3>Position Details</h3>
                    <p><strong>Job Title:</strong> {{ $vacancyTitle }}</p>
                    <p><strong>Location:</strong> {{ $vacancyLocation }}</p>
                </div>

                <p class="message">
                    Our HR team will contact you shortly with further details regarding your
                    onboarding process, start date, and other relevant information.
                </p>

                <p class="message">
                    We look forward to welcoming you to our team!
                </p>
            @elseif($isRejected)
                <p class="message">
                    Thank you for your interest in the position of <strong>{{ $vacancyTitle }}</strong>
                    and for taking the time to go through our application process.
                </p>

                <div class="status-badge status-rejected">
                    Application Not Selected
                </div>

                <p class="message">
                    After careful consideration and review of all applications, we regret to inform you
                    that we will not be moving forward with your application at this time.
                </p>

                <div class="job-details">
                    <h3>Applied Position</h3>
                    <p><strong>Job Title:</strong> {{ $vacancyTitle }}</p>
                    <p><strong>Location:</strong> {{ $vacancyLocation }}</p>
                </div>

                <p class="message">
                    Please know that this decision was not easy, and we genuinely appreciate the time
                    and effort you invested in your application. We were impressed by your credentials
                    and encourage you to apply for future opportunities that match your skills and experience.
                </p>

                <p class="message">
                    We wish you all the best in your job search and your future career endeavors.
                </p>
            @else
                <p class="message">
                    Your application status for the position of <strong>{{ $vacancyTitle }}</strong>
                    has been updated.
                </p>

                <div class="job-details">
                    <h3>Application Details</h3>
                    <p><strong>Job Title:</strong> {{ $vacancyTitle }}</p>
                    <p><strong>Location:</strong> {{ $vacancyLocation }}</p>
                    <p><strong>Current Status:</strong> {{ $statusLabel }}</p>
                </div>

                <p class="message">
                    We will keep you informed as your application progresses through our recruitment process.
                </p>
            @endif

            <hr class="divider">

            <p class="message" style="font-size: 14px;">
                If you have any questions or need further information, please don't hesitate to contact
                our HR department.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>&copy; {{ date('Y') }} All rights reserved.</p>
            <p style="margin-top: 15px;">
                This is an automated message, please do not reply to this email.
            </p>
        </div>
    </div>
</body>

</html>
