<?php
  $subdomain = 'boriscom123'; //Поддомен нужного аккаунта
  $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/companies'; //Формируем URL для запроса
  /** Получаем access_token из файла amointegrationapi.json */
  $token = explode("/",file_get_contents("amointegrationapi.json"));
  $access_token = json_decode($token[0], true)['access_token'];
  // Добавление компании
  /** Подготовка запроса к БД */
  $headers = [
    'Authorization: Bearer ' . $access_token
  ];
  $new_compani = '[
    {
        "name": "'. $_POST['compani'] .'"
    }
  ]';
  $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
  //Устанавливаем необходимые опции для сеанса cURL
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
  curl_setopt($curl, CURLOPT_URL, $link);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl, CURLOPT_POSTFIELDS, $new_compani);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  $code = (int)$code;
  $errors = [
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found',
    500 => 'Internal server error',
    502 => 'Bad gateway',
    503 => 'Service unavailable',
  ];

  try
  {
    /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
    if ($code < 200 || $code > 204) {
      throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
    }
  }
  catch(\Exception $e)
  {
    die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
  }
?>
