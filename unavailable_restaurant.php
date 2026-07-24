<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Restaurants Available</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .all-restaurants-grid {
            display: flex !important;
        }

        .no-restaurants-container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .icon-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 20px rgba(255, 154, 158, 0.3);
        }

        .icon-container svg {
            width: 60px;
            height: 60px;
            fill: white;
        }

        h2 {
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 28px;
        }

        .message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .suggestions {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
        }

        .suggestions h3 {
            color: #444;
            margin-bottom: 12px;
            font-size: 18px;
            display: flex;
            align-items: center;
        }

        .suggestions h3 svg {
            margin-right: 8px;
            fill: #ff7e5f;
        }

        .suggestions ul {
            list-style-type: none;
            padding-left: 5px;
        }

        .suggestions li {
            padding: 8px 0;
            color: #555;
            display: flex;
            align-items: flex-start;
        }

        .suggestions li::before {
            content: '•';
            color: #ff7e5f;
            font-weight: bold;
            margin-right: 10px;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 50px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        .btn-primary {
            background: linear-gradient(90deg, #ff7e5f, #feb47b);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 126, 95, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 126, 95, 0.4);
        }

        .btn-secondary {
            background-color: #f0f0f0;
            color: #555;
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        .btn svg {
            margin-right: 8px;
        }

        @media (max-width: 480px) {
            .no-restaurants-container {
                padding: 30px 20px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="no-restaurants-container">
        <div class="icon-container">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M8.1 13.34l2.83-2.83L3.91 3.5c-1.56 1.56-1.56 4.09 0 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 13l1.47-1.47z" />
            </svg>
        </div>

        <h2>No Restaurants Available</h2>

        <p class="message">
            We're sorry, but there are currently no restaurants available in your area.
            This could be due to your location, time of day, or temporary service issues.
        </p>

        <!-- <div class="suggestions">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                </svg>
                Suggestions:
            </h3>
            <ul>
                <li>Try expanding your search radius</li>
                <li>Check your internet connection</li>
                <li>Try again during regular business hours</li>
                <li>Consider a different delivery service</li>
            </ul>
        </div>
         -->
        <!-- <div class="actions">
            <button class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                    <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.46-4.17-3.77-7.48-7.94-7.94V1h-2v2.06C6.83 3.52 3.52 6.83 3.06 11H1v2h2.06c.46 4.17 3.77 7.48 7.94 7.94V23h2v-2.06c4.17-.46 7.48-3.77 7.94-7.94H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
                </svg>
                Try Another Location
            </button>
            <button class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                    <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                </svg>
                Refresh
            </button>
        </div> -->
    </div>
</body>

</html>