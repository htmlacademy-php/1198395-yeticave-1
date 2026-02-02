<?php

/**
 * Проверяет файл, который пользователь добавил в форму. При успешной валидации загружает файл на сервер и возвращает путь к файлу на сервере,
 * при неуспешной - возвращает сообщение ошибки.
 *
 * @param string $filename Имя файла в системе пользователя.
 *
 * @return array Ассоциативный массив с ключами:
 * `success` - `bool` успешно ли загружен файл;
 * `error` - описание ошибки загрузки;
 * `imgPath` - путь к файлу на сервере.
 */
function uploadImg(string $filename): array
{
    $result =
        [
            'success' => false,
            'error' => '',
            'imgPath' => '',
        ];

    if (!isset($_FILES[$filename], $_FILES[$filename]['tmp_name'], $_FILES[$filename]['size'])
        || empty($_FILES[$filename]['tmp_name'])) {
        $result['error'] = 'Загрузите картинку в формате "jpeg" или "png".';
    }

    if (empty($result['error']) && $fileInfo = finfo_open(FILEINFO_MIME_TYPE)) {
        $fileTempName = $_FILES[$filename]['tmp_name'];
        $fileSize = $_FILES[$filename]['size'];
        $fileType = finfo_file($fileInfo, $fileTempName);
        $acceptedTypes =
            [
                'image/jpeg' => '.jpg',
                'image/png' => '.png',
            ];

        switch (true) {
            case !isset($acceptedTypes[$fileType]) :
                $result['error'] = 'Загрузите картинку в формате "jpeg" или "png".';
                break;
            case $fileSize > 2000000 :
                $result['error'] = 'Максимальный размер файла: 2МБ.';
                break;
            default:
                $fileType = $acceptedTypes[$fileType];
                $rootPath = $_SERVER['DOCUMENT_ROOT'] ?? '';
                $filePath = '/uploads/' . uniqid() . $fileType;

                move_uploaded_file($fileTempName, "$rootPath/$filePath")
                    ? $result['imgPath'] = $filePath
                    : $result['error'] = 'Ошибка при загрузке файла.';

                $result['success'] = empty($result['error']) && !empty($result['imgPath']);
                break;
        }
    }

    return $result;
}
