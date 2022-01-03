<?php


header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Detecta el metodo http
$method = $_SERVER['REQUEST_METHOD'];

// Recupera los uri params adicionales al endpoint
if (!empty($_SERVER['PATH_INFO'])) {
    $pathParams = explode("/", substr($_SERVER['PATH_INFO'], 1));
}

/*
// Recupera todos los componentes de la url
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
$id = $uri[3];
echo $id;
*/

$endpoint = "https://jsonplaceholder.typicode.com/users";

switch ($method) {
    case 'GET':
        $responseCode = null;
        $result = null;

        $client = curl_init();
        curl_setopt($client, CURLOPT_HTTPGET, 1);
        curl_setopt($client, CURLOPT_HTTPHEADER, array('Content Type: application/json'));
        //curl_setopt($client, CURLOPT_SSLVERSION, CURL_TLSV1_2);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);

        // Comprueba si el query param search esta informado y busca el elemento por su name
        $query = "";
        if(!empty($_GET['name'])){
            $query = $query . "name=" . $_GET['name'];
        } 
        if (!empty($_GET['username'])){
            $query = $query . "username=" . $_GET['username'];
        }
        if (!empty($_GET['email'])) {
            $query = $query . "email=" . $_GET['email'];
        }
        
        // Busca un elemento por atributo
        if ($query !== "") {
            curl_setopt($client, CURLOPT_URL, $endpoint . "?" . $query);
        }
        // Busca un elemento por su id (se comprueba si el primer path param esta informado)
        else if (isset($pathParams[0])) {
            $id = $pathParams[0];
            curl_setopt($client, CURLOPT_URL, $endpoint . "/" . $id);
        }
        // Lista todos los elementos
        else {
            curl_setopt($client, CURLOPT_URL, $endpoint);
        }

        $response = curl_exec($client);
        $responseCode = curl_getinfo($client, CURLINFO_HTTP_CODE);
        curl_close($client);

        // Se da formato al json de la respuesta de la peticion
        //$result = '{ "data": ' . $response . '}';
        $result = $response;

        // Verifica si la respuesta esta vacia, ya que esta deberia ser un 204 y no 200
        $data = json_decode($response);
        if (empty($data)) {
            $responseCode = 204;
        }
        
        // Informa el resultado
        header('Content-Type: application/json');
        http_response_code($responseCode);
        if ($responseCode != null && $responseCode == 200){
            echo $result;
        }

        break;
    case 'POST':
        // Recupera el json de entrada de la peticion y lo convierte en array
        $data = json_decode(file_get_contents('php://input'));

        //echo print_r((array) $data);

        // Convierte el array de entrada en json
        $payload = json_encode((array) $data);
        
        //$myData = array("name" => "Julio");
        //$payload = json_encode(array("data" => $myData));
        //echo $payload;

        $client = curl_init();
        curl_setopt($client, CURLOPT_URL, $endpoint);
        //curl_setopt($client, CURLOPT_POST, true);
        curl_setopt($client, CURLOPT_HTTPHEADER, array('Content Type: application/json'));
        curl_setopt($client, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($client);
        $responseCode = curl_getinfo($client, CURLINFO_HTTP_CODE);
        curl_close($client);

        // Por alguna razon, php recibe mal el json de respuesta,
        // por lo que hace falta reformatearlo para mostrar la salida correcta
        $output = json_decode($response);

        // Se convierte el objeto en array para eliminar el id y extraer la llave
        $output = (array) $output;
        $newId = $output["id"];
        unset($output["id"]);

        // Se extrae la llave que contiene el json de salida y se convierte en array
        foreach ($output as $k => $v) {
            //echo "[$k] => $v \n";
            $output = (array) json_decode($k);
        }

        // Se inserta la llave en el nuevo array
        $output["id"] = $newId;

        // Se da formato al json de la respuesta de la peticion
        $result = '{ "data": ' . json_encode($output) . '}';

        header('Content-Type: application/json');
        http_response_code($responseCode);
        if ($responseCode == 201){
            echo $result;
        }

        break;

    case 'OPTIONS':
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        http_response_code(204);

        break;
    default:
        http_response_code(405);
        break;
  }



?>