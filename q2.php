<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dynamic Image Gallery</title>
    <style>
        * {
            margin: 0;
        }
        .area {
            width: 99%;
            margin-left: 0.5%;
            border-radius: 10px;
        }
        .gallery .contents {
            display: flex;
            justify-content: flex-end;
            height: 150px;
            width: 80%;
            border: solid rgb(128, 128, 128) 1px;
            margin: 10px 0 10px 10%;
        }
        .gallery .contents img {
            padding: 30px;
        }
        .gallery .contents .p1 {
            font-size: 20px;
            padding-top: 30px;
            text-align: right;
            font-weight: bolder;
        }
        .gallery .contents div {
            width: 100%;
        }
        .gallery .contents .p2 {
            font-size: 17px;
            display: flex;
            justify-content: flex-start;
            margin-left: 10px;
            color: rgb(128, 128, 128);
        }
        .buttons {
            width: 50%;
            display: flex;
            justify-content: center;
            margin: 10px 0 10px 25%;
        }
        .buttons button {
            height: 32px;
            background-color: rgb(103, 211, 98);
            color: white;
            border-radius: 30px;
            margin: 0 5px;
        }
        .forlogo {
            display: flex;
            justify-content: flex-start;
            width: 80%;
            margin-left: 10%;
            border-bottom: 2px solid rgb(128, 128, 128);
        }
        .forlogo img {
            width: 120px;
        }
        .generate {
            height: 50px;
            background-color: rgb(103, 211, 98);
            color: white;
            border-radius: 15px;
            width: 99%;
            margin-left: 0.5%;
        }
    </style>
</head>
<body>
<p style="font-size: 25px">Write your instruction in JSON or XML</p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <textarea class="area" name="configData" rows="10"></textarea>
    <button type="submit" class="generate">Generate Page</button>
</form>
<div class="forlogo"> <img src="logo/Logo2.png"></div>

<div class="gallery">
    <?php
    $imagesPerPage = 3;

    $localPath = 'images';
    $textFilePath = 'captions/captions.txt';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $configData = $_POST['configData'];

        if (trim($configData[0]) == '{') {
            $config = json_decode($configData, true);
            $imagesPerPage = $config['page_conf']['ipp'];
            $localPath = $config['page_conf']['imgs_folder'];
            $textFilePath = $config['page_conf']['caption_file'];
        } elseif (trim($configData[0]) == '<') {
            $config = simplexml_load_string($configData);
            $imagesPerPage = (int)$config->ipp;
            $localPath = (string)$config->imgs_folder;
            $textFilePath = (string)$config->caption_file;
        }
    }

    $basePath = 'http://localhost/12116027/images/';

    $textData = file_get_contents($textFilePath);
    $entries = explode("#", $textData);
    $quotes = [];
    foreach ($entries as $entry) {
        if (trim($entry) != "") {
            list($id, $quote, $author) = explode(";", $entry);
            $quotes[trim($id)] = ['quote' => trim($quote), 'author' => trim($author)];
        }
    }

    $allFiles = array_diff(scandir($localPath), array('..', '.'));
    $images = array_filter($allFiles, function($file) use ($localPath) {
        $file_path = $localPath . '/' . $file;
        return is_file($file_path) && in_array(pathinfo($file_path, PATHINFO_EXTENSION), ['jpg', 'png', 'gif', 'jpeg']);
    });

    $totalImages = count($images);
    $totalPages = ceil($totalImages / $imagesPerPage);

    $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
    $currentPage = max(1, min($currentPage, $totalPages));

    $start = ($currentPage - 1) * $imagesPerPage;

    foreach (array_slice($images, $start, $imagesPerPage) as $index => $image) {
        $imageUrl = $basePath . $image;
        $imageIndex = $start + $index + 1;

        echo '<div class="contents">';
        if (isset($quotes[$imageIndex])) {
            echo '<div><p class="p1">' . htmlspecialchars($quotes[$imageIndex]['quote']) . '</p>';
            echo '<p class="p2">' . htmlspecialchars($quotes[$imageIndex]['author']) . '</p></div>';
        }
        echo '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($image) . '">';
        echo '</div>';
    }

    echo '<div class="buttons">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $buttonWidth = (100 / $totalPages) - (10 * ($totalPages - 1) / $totalPages);
        echo '<button style="width: ' . $buttonWidth . '%;" onclick="window.location.href=\'?page=' . $i . '\'">' . $i . '</button>';
    }
    echo '</div>';

    ?>
</div>

</body>
</html>
