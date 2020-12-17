<?php
  // echo '1. Получаем информацию<br>';
  $subdomain = 'zakirov'; //Поддомен нужного аккаунта
  $link = 'https://' . $subdomain . '.amocrm.ru/api/v2/account'; //Формируем URL для запроса
  /** Получаем access_token из хранилища */
  $page_title = 'amo-crm';
  $amo_crm_page = get_page_by_title( $page_title );
  $response = json_decode($amo_crm_page->post_content, true);
  $access_token = $response['access_token'];
  /** Формируем заголовки */
  // echo $access_token;
  $headers = [
    'Authorization: Bearer ' . $access_token
  ];
  $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
  /** Устанавливаем необходимые опции для сеанса cURL  */
  curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
  curl_setopt($curl,CURLOPT_URL, $link);
  curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl,CURLOPT_HEADER, false);
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
  $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  // print_r($out);
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


  // echo "<br>2. Добавление сделки<br>";
  $lead_link = 'https://' . $subdomain . '.amocrm.ru/api/v4/leads'; // добавление сделки
  /** Подготовка запроса к БД */
  $new_lead = '[
    {"name": "Сделка с сайта Фотопозитив"}
    ]';
  $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
  //Устанавливаем необходимые опции для сеанса cURL
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
  curl_setopt($curl, CURLOPT_URL, $lead_link);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl, CURLOPT_POSTFIELDS, $new_lead);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
  $out_link = json_decode($out, true);
  // echo "id сделки: ";
  $id_link = $out_link['_embedded']['leads'][0]['id'];
  // echo $id_link;

  // echo "<br>3. Получаем ID дополнительных полей контакта<br>";
  $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/contacts/custom_fields'; // дополнительные поля контакта
  $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
  /** Устанавливаем необходимые опции для сеанса cURL  */
  curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
  curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
  curl_setopt($curl,CURLOPT_URL, $link);
  curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($curl,CURLOPT_HEADER, false);
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
  $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  // print_r($out);

  // echo "<br>4. Добавляем контакт<br>";
  $contact_link = 'https://' . $subdomain . '.amocrm.ru/api/v4/contacts'; // добавление контакта
  $id = json_decode($out, true);
  $id_tel = $id['_embedded']['custom_fields'][1]['id'];
  $id_email = $id['_embedded']['custom_fields'][2]['id'];
  $data = '[
    {"first_name" : "'.$_POST['last-name'].'", "custom_fields_values": [
      {"field_id": '.$id_tel.', "values": [
        {"value": "'.$_POST['tel'].'", "enum_code": "HOME"}
        ] },
      {"field_id": '.$id_email.', "values": [
        {"value": "'.$_POST['email'].'", "enum_code": "PRIV"}
        ] }
      ] }
    ]';
  // echo $data;
  $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
  /** Устанавливаем необходимые опции для сеанса cURL  */
  curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
  curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
  curl_setopt($curl,CURLOPT_URL, $contact_link);
  curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
  curl_setopt($curl,CURLOPT_HEADER, false);
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
  $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  // print_r($out);
  $out_contact = json_decode($out, true);
  // echo "Выводим ID нового пользователя: ";
  $id_contact = $out_contact['_embedded']['contacts'][0]['id'];
  // echo $id_contact;
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
  // echo "<br>5. Связываем контакт со сделкой<br>";
  $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/leads/'.$id_link.'/link'; // дополнительные поля контакта
  $data = '[
    {"to_entity_id": '.$id_contact.', "to_entity_type": "contacts"}
    ]';
  // echo $data;
  $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
  /** Устанавливаем необходимые опции для сеанса cURL  */
  curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
  curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
  curl_setopt($curl,CURLOPT_URL, $link);
  curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
  curl_setopt($curl,CURLOPT_HEADER, false);
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
  $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  // print_r($out);
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
