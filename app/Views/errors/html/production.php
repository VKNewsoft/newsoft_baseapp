<!-- <!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex">

	<title>Whoops!</title>

	<style type="text/css">
		<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
	</style>
</head>
<body>

	<div class="container text-center">

		<h1 class="headline">Whoops!</h1>

		<p class="lead">We seem to have hit a snag. Please try again later...</p>

	</div>

</body>

</html> -->

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oops! Terjadi Kesalahan</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            text-align: center;
            padding: 50px;
        }

        .container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: #dc3545;
            font-size: 26px;
            font-weight: 600;
        }

        p {
            font-size: 16px;
            color: #555;
            margin: 10px 0;
        }

        .icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 10px;
            animation: bounce 1s infinite alternate;
        }

        @keyframes bounce {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(-5px);
            }
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            transition: 0.3s;
            margin-top: 15px;
        }

        .back {
            background-color: #007bff;
        }

        .back:hover {
            background-color: #0056b3;
        }

        .contact {
            background-color: #28a745;
        }

        .contact:hover {
            background-color: #218838;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>Oops! Terjadi Kesalahan</h1>
        <p>Mohon maaf, terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti atau hubungi tim IT kami jika masalah masih berlanjut.</p>
        <a href="mailto:it.team2@indopasifik.id" class="button contact">📧 Hubungi Dukungan</a>
    </div>
</body>
</html>
