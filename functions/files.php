<?php

/**
 * Проверяет файл, который пользователь добавил в форму. При успешной валидации загружает файл на сервер и возвращает путь к файлу на сервере,
 * при неуспешной - возвращает сообщение ошибки.
 * @param string $filename Имя файла в системе пользователя.
 *
 * @return array Массив, состоящий из статуса загрузки файла, сообщения об ошибке и пути к файлу на сервере.
 */
function uploadImg(string $filename): array
{
    $error = '';
    $imgPath = '';

    if (!empty($_FILES[$filename]['tmp_name'])) {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileTempName = $_FILES[$filename]['tmp_name'];
        $fileSize = $_FILES[$filename]['size'];

        $fileType = finfo_file($fileInfo, $fileTempName);

        $acceptedTypes =
            [
                'image/jpeg' => '.jpg',
                'image/png' => '.png',
            ];

        if (!isset($acceptedTypes[$fileType])) {
            $error = 'Загрузите картинку в формате "jpeg" или "png".';
        } elseif ($fileSize > 2000000) {
            $error = 'Максимальный размер файла: 2МБ.';
        } else {
            $fileType = $acceptedTypes[$fileType];
            $rootPath = $_SERVER['DOCUMENT_ROOT'];
            $filePath = '/uploads/' . uniqid() . $fileType;

            move_uploaded_file($fileTempName, "$rootPath/$filePath")
                ? $imgPath = $filePath
                : $error = 'Ошибка при загрузке файла.';
        }
    } else {
        $error = 'Загрузите картинку в формате "jpeg" или "png".';
    }

    return [
        'success' => empty($error) && !empty($imgPath),
        'error' => $error,
        'imgPath' => $imgPath,
    ];
}
