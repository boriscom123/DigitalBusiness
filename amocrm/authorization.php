<?php
  $subdomain = 'boriscom123'; //Поддомен нужного аккаунта
  $link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса
  /** Соберем данные для запроса */
  $data = [
  	'client_id' => '4b4fac95-42a1-4b60-ac65-31884f728e7c',
  	'client_secret' => 'tDGoVqZ6OcrEzd91opz01XIMLnVqjDzjsKbV4Z3ciTBfbQZjf1im0H8pfAguPfeJ',
  	'grant_type' => 'authorization_code',
  	'code' => 'def5020029a203130c9ec6556b5e979d96c57a938d968b989f24c4911b72b04530082631d4869b291c828c556c88cea92a4aa69bf14f72a495bf0955a7ed1f0cb307b433f7323c6202b0d5ca7ae1ad4f84415f7753eb81f448c487864ea26205d2e2651d25411e23b31ef3dbeebf7cc8bc9d73abcd8c45db956bf1b5bb08c5481b138fbb8bdedaaf20b439f6e5e6207c3256b82e93b4afe8060b1aec54d5e716922ef5ba7438e6126a034f391c7c0e839973aba2fe8619d7437c77560f4d8a49a6c3beb8782e26503ee57921e0cc7c0a798bea23b31c5018e14797092944caf6d8086d0294e8c5f08fa35afdc94cb683b4c61c4f3734228578608f4b501390d081a76a5e80249c0ff2e793e8997a977a46fabeea98fd59cf5782917ee4a107fe4891786d0a0aabd2c3c379c125a35548f5c34f40f3b96bfd58023a4d1ef3778a05ec876601fade0c6cb8dd5aac65fc7802d2bf78adef04ca4cac2945bdb9c68d619cf2f3730cac8099594d998d8f95be763191a74c9abf0c86311bf3c68a0ca5ba7c98a2a1fe81870665d4fc2625c15726854ebb02f11f16ef5a41227c993380c509a68bc613285b3481a7566229d1838d8e506e69e095dbe2c166b8',
  	'redirect_uri' => 'http://bynext.ru/',
  ];

  /**
   * Нам необходимо инициировать запрос к серверу.
   * Воспользуемся библиотекой cURL (поставляется в составе PHP).
   * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
   */
  $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
  /** Устанавливаем необходимые опции для сеанса cURL  */
  curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
  curl_setopt($curl,CURLOPT_URL, $link);
  curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
  curl_setopt($curl,CURLOPT_HEADER, false);
  curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
  $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  /** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
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
  // сохраняем ответ от сервера в файл.
  $addtofile = $out.'/{"until":'. ($_SERVER['REQUEST_TIME'] + 86400) .'}';
  $handle = fopen("amointegrationapi.json", "a");
  fwrite($handle, $addtofile);
  fclose($handle);
  /**
   * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
   * нам придётся перевести ответ в формат, понятный PHP
   */
  // $response = json_decode($out, true);
?>
