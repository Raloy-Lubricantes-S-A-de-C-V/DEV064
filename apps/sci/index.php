<?php

if (extension_loaded('soap')) {

    class Contact {

        public function __construct($id, $name) {
            $this->id = $id;
            $this->name = $name;
        }

    }

    /* Initialize webservice with your WSDL */
    $client = new SoapClient("http://localhost:10139/Service1.asmx?wsdl");

    /* Fill your Contact Object */
    $contact = new Contact(100, "John");

    /* Set your parameters for the request */
    $params = array(
        "Contact" => $contact,
        "description" => "Barrel of Oil",
        "amount" => 500,
    );

    /* Invoke webservice method with your parameters, in this case: Function1 */
    $response = $client->__soapCall("Function1", array($params));

    /* Print webservice response */
    var_dump($response);
}
