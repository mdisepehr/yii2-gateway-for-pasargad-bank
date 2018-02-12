Yii2 Gateway for Pasargad Bank
==============================
Yii2 Gateway for Pasargad Bank

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mdisepehr/yii2-gateway-for-pasargad-bank "dev-master"
```

or add

```
"mdisepehr/yii2-gateway-for-pasargad-bank": "dev-master"
```

to the require section of your `composer.json` file.


Usage
-----

for first:

```php
<?php
 $merchantCode=111111;
 $terminalCode=222222;
 $privateKey='your private key';
 
 $pay=new \mdisepehr\pasargad\PasargadGatewayClass($merchantCode,$terminalCode,$privateKey);
 ?>
```

Request
-----
simple example:

```php
<?php
$invoiceNumber=12;
$invoiceDate=date("Y/m/d H:i:s");
$amount=1000;
$redirectAddress="http://YourDomain.com/payment/verify";

$payReq=$pay->sendOrder($invoiceNumber, $invoiceDate, $amount, $redirectAddress);

?>
```

Verify
-----
simple example in your controller:

```php
<?php
if($pay->verifyOrder()){
    $array=$pay->getOrder(\Yii::$app->request->get('tref',0));
    return $this->render('success',$array);
}
else{
    return $this->render('error');
}
?>
```