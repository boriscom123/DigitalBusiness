<?php
  if(!empty($_POST)){
    if(isset($_POST['create']) && ($_POST['create'] === "Создать сделку")){
      include 'amocrm/newlead.php';
      $out_lead_link = json_decode($out, true);
      $id_lead = $out_lead_link['_embedded']['leads'][0]['id'];
      // echo "id сделки: ".$id_lead."<br>";
      if(isset($_POST['info']) && ($_POST['info'] !== '')) {
        // создаем примечание и связываем со сделкой
        include 'amocrm/newnote.php';
      }
      if(isset($_POST['compani']) && ($_POST['compani'] !== '')) {
        // создаем компанию
        include 'amocrm/newcompani.php';
        $out_compani_link = json_decode($out, true);
        $id_company = $out_compani_link['_embedded']['companies'][0]['id'];
        // echo "id компании: ".$id_company."<br>";
        // связываем компанию со сделкой
        include 'amocrm/leadlinkcompani.php';
      }
      if(isset($_POST['name']) && ($_POST['name'] !== '')) {
        // создаем контакт
        include 'amocrm/newcontact.php';
        $out_contact_link = json_decode($out, true);
        $id_contact = $out_contact_link['_embedded']['contacts'][0]['id'];
        // echo "id контакта: ".$id_contact."<br>";
        // связываем контакт со сделкой
        include 'amocrm/leadlinkcontact.php';
        // связываем контакт с компанией
        include 'amocrm/companilinkcontact.php';
      }
      // создаем задачу для ответственного менеджера в рамках тестовой сделки
      include 'amocrm/newtask.php';
    }
  }
  if(!file_exists('amointegrationapi.json')){
    // echo "Делаем новую интеграцию";
    include 'amocrm/authorization.php';
  } else {
    // echo "Используем имеющийся токен";
    $token = explode("/",file_get_contents("amointegrationapi.json"));
    if(json_decode($token[1], true)['until'] < $_SERVER['REQUEST_TIME']){
      // echo "Токен просрочен";
      include 'amocrm/refreshtoken.php';
    }
    $access_token = json_decode($token[0], true)['access_token'];
  }
?>
<!DOCTYPE html>
<html lang="ru" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Тестовое задание</title>
    <link rel="stylesheet" href="/style/index.css">
  </head>
  <body>
    <h1><a href="index.php">На главную</a></h1>
    <div class="leads-content" id="leads">
      <?php // Вывести на экран список всех сделок со связанными контактами и компаниями
      if(file_exists('amointegrationapi.json')) {
        //echo "Запрашиваем информацию по сделкам из амо<br>";
        include 'amocrm/getleads.php';
        $leads = json_decode($out);
        // print_r($leads);
        foreach($leads->_embedded->leads as $value){
          echo "<div class='leads-list'>";
          echo "<div><h2>Сделка: ".$value->name."</h2></div>";
          $lead_id = $value->id;
          // запрашиваем примечания
          include 'amocrm/getnotes.php';
          $notes = json_decode($out);
          if(!empty($notes)){
            foreach($notes->_embedded as $notes_value){
              echo "<p>Примечание: ".$notes_value[0]->params->text."</p>";
            }
          }
          // запрашиваем связанные данные
          include 'amocrm/getleadslink.php';
          $links = json_decode($out);
          $user_id = $value->responsible_user_id;
          include 'amocrm/getuser.php';
          $user = json_decode($out);
          // print_r($user);
          echo "<p>Ответственный менеджер: ".$user->name."</p>";
          $contact_number = 0;
          foreach($links->_embedded->links as $value){
            if($value->to_entity_type === "contacts") {
              $contact_id = $value->to_entity_id;
              include 'amocrm/getcontact.php';
              $contact = json_decode($out);
              // print_r($contact);
              echo "<p>Имя контакта ".++$contact_number.": ".$contact->name."</p>";
              if(!empty($contact->custom_fields_values)) {
                foreach($contact->custom_fields_values as $value){
                  if($value->field_name === "Телефон"){
                    echo "<p>Телефон: ".$value->values[0]->value."</p>";
                  }
                }
                foreach($contact->custom_fields_values as $value){
                  if($value->field_name === "Email"){
                    echo "<p>Email: ".$value->values[0]->value."</p>";
                  }
                }
              } else {
                echo "<p>Нет контактных данных</p>";
              }
            }
          }
          foreach($links->_embedded->links as $value){
            if($value->to_entity_type == "companies") {
              $company_id = $value->to_entity_id;
              include 'amocrm/getcompani.php';
              $company = json_decode($out);
              echo "<p>Название компании: ".$company->name."</p>";
            }
          }
          echo "</div>";
        }
      }
      ?>
    </div>
    <div class="new-lead">
      <form class="" action="index.php" method="post">
        <h2>Новая сделка</h2>
        <p>Примечание к сделке</p>
        <div class="">
          <input type="text" name="info" value="" placeholder="Примечание">
        </div>
        <p>Компания: Наименование</p>
        <div class="">
          <input type="text" name="compani" value="" placeholder="Компания: Наименование">
        </div>
        <p>Контакт: Имя</p>
        <div class="">
          <input type="text" name="name" value="" placeholder="Контакт: Имя">
        </div>
        <div class="">
          <input type="submit" name="create" value="Создать сделку">
        </div>
      </form>
    </div>
    <div class="" id="contacts">
      <?php // Выводим контакты
      if(file_exists('amointegrationapi.json')) {
        //echo "Запрашиваем информацию по контактам из амо<br>";
        include 'amocrm/getallcontacts.php';
        $contacts = json_decode($out);
        // print_r ($contacts);
        foreach($contacts->_embedded->contacts as $value){
          //echo "<div><h2>контакт</h2></div>";
          //print_r($value);
          //echo "<br>";
        }
      }
      ?>
    </div>
    <div class="" id="companies">
      <?php // Выводим компании
      if(file_exists('amointegrationapi.json')) {
        //echo "Запрашиваем информацию по контактам из амо<br>";
        include 'amocrm/getallcompanies.php';
        $contacts = json_decode($out);
        // print_r ($contacts);
        foreach($contacts->_embedded->companies as $value){
          //echo "<div><h2>Компания</h2></div>";
          //print_r($value);
          //echo "<br>";
        }
      }
      ?>
    </div>
    <script src="script/index.js"></script>
  </body>
</html>
