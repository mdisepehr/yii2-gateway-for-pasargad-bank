<?php
/**
 * Created by PhpStorm.
 * User: msmse
 * Date: 12/02/2018
 * Time: 11:43 AM
 */

namespace mdisepehr\pasargad;


class PasargadGatewayClass
{
    public $merchantCode;
    public $terminalCode;
    public $privateKey;

    function __construct($merchantCode,$terminalCode,$privateKey){
        $this->merchantCode=$merchantCode;
        $this->terminalCode=$terminalCode;
        $this->privateKey=$privateKey;
    }

    public function sendOrder($invoiceNumber = NULL, $invoiceDate = NULL, $amount = NULL, $redirectAddress){
        $processor = new RSAProcessor($this->privateKey,RSAProcessor::XMLString);
        date_default_timezone_set('Asia/Tehran');
        $timeStamp = date("Y/m/d H:i:s");
        $action = "1003";

        \Yii::$app->session->set('pasargadAmount',$amount);

        $data = "#". $this->merchantCode ."#". $this->terminalCode ."#". $invoiceNumber ."#". $invoiceDate ."#". $amount ."#". $redirectAddress ."#". $action ."#". $timeStamp ."#";
        $data = sha1($data,true);
        $data =  $processor->sign($data);
        $result =  base64_encode($data);
        echo "
			<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
			<html>
			  	<body>
			  		<div id='WaitToSend' style='margin:0 auto; width: 600px; text-align: center;'>درحال انتقال به درگاه بانک<br>لطفا منتظر بمانید .</div>
					<form Id='GateWayForm' Method='post' Action='https://pep.shaparak.ir/gateway.aspx' style='display: none;'>
						invoiceNumber<input type='text' name='invoiceNumber' value='$invoiceNumber' />
						invoiceDate<input type='text' name='invoiceDate' value='$invoiceDate' />
						amount<input type='text' name='amount' value='$amount' />
						terminalCode<input type='text' name='terminalCode' value='$this->terminalCode' />
						merchantCode<input type='text' name='merchantCode' value='$this->merchantCode' />
						redirectAddress<input type='text' name='redirectAddress' value='$redirectAddress' />
						timeStamp<input type='text' name='timeStamp' value='$timeStamp' />
						action<input type='text' name='action' value='$action' />
						sign<input type='text' name='sign' value='$result' />
					</form>
					<script language='javascript'>document.forms['GateWayForm'].submit();</script>
				</body>
			</html>";
    }

    public function getOrder($tref = NULL){
        if(isset($tref)){
            $fields = array('invoiceUID' => $tref );
            $parser=new Parser();
            $result = $parser->post2https($fields,'https://pep.shaparak.ir/CheckTransactionResult.aspx');
            $array = $parser->xml2array($result);
            if($array["resultObj"]["result"] == "True"){
                return $array;
            }
            else
                return false;
        }
        else
            return false;
    }

    public function verifyOrder(){
        $amount=\Yii::$app->session->get('pasargadAmount',0);
        $get=\Yii::$app->request;
        $fields=[
            'MerchantCode' => $this->merchantCode,
            'TerminalCode' => $this->terminalCode,
            'InvoiceNumber' => $get->get('iN'),
            'InvoiceDate' => $get->get('iD'),
            'amount' => $amount,
            'TimeStamp' => date("Y/m/d H:i:s"),
            'sign' => ''
        ];

        $processor = new RSAProcessor($this->privateKey ,RSAProcessor::XMLString);

        $data = "#". $fields['MerchantCode'] ."#". $fields['TerminalCode'] ."#". $fields['InvoiceNumber'] ."#". $fields['InvoiceDate'] ."#". $fields['amount'] ."#". $fields['TimeStamp'] ."#";
        $data = sha1($data,true);
        $data =  $processor->sign($data);
        $fields['sign'] =  base64_encode($data);

        $parser=new Parser();
        $verifyresult = $parser->post2https($fields,'https://pep.shaparak.ir/VerifyPayment.aspx');
        $array = $parser->xml2array($verifyresult);

        if($array['actionResult']['result'] == "True")
            return true;
        else
            return false;

    }
}