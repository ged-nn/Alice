<?php


class Answer{
    public $text='';
    public $tts='';
    public $response;
    public $data;
    public $buton;
    public function result() {
        
        if (strlen($this->tts)==0)
            $this->tts=$this->txt;
        $answer['response']['text'] = $this->txt;
        $answer['response']['tts'] = $this->tts;
        $answer["response"]["buttons"] = array();
        $answer["response"]["end_session"] = false;
        $answer["session"]["session_id"]=$this->data->session->session_id;
        $answer["session"]["message_id"]=$this->data->session->message_id;
        $answer["session"]["user_id"]=$this->data->session->user_id;
        $answer["version"] = $this->data->version;
        return $answer;
          /*  
            $answer['response']['buttons'] = array(
            array(
                'title' => 'позвони',
                'payload' => array('opt' => 'dial'),
            ),
            array(
                'title' => 'управлять делами',
                'payload' => array('opt' => 'todo'),
            ),
            array(
                'title' => 'другой выбор',
                'payload' => array('opt' => 'more'),
            ),
        );
        */
    }
    public function __construct($data,$txt="",$tts="",$button="") {
        $this->data=$data;
		$this->txt=$txt;
		$this->tts=$tts;
	}
}

function dial($PHONE_DIAL){
    include_once 'dial.cfg';

    debug($URL.'?phone='.$PHONE_DIAL.'&token='.$PASSWD.'&user='.$USER,0,"URL:");
    $ch = curl_init($URL.'?phone='.$PHONE_DIAL.'&token='.$PASSWD.'&user='.$USER);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    
    $curl_out=curl_exec($ch);
    curl_close($ch);
}

function debug($text,$var=false,$prefix="")
{
	$file='debug.log';
	if (!$var)
		$message=$text;
	else
		$message=var_export($text,true);
    $message=date("Y-m-d H:i:s:v").": ".$message;
	if (strlen($prefix)>0)
		$message=$prefix.$message;

	file_put_contents($file, $message."
", FILE_APPEND);
}

if (!isset($_REQUEST)) return;
//Получаем и декодируем уведомление
$dataRow = file_get_contents('php://input');
$data = json_decode($dataRow);

debug($data,1, "Получили от алисы: ");

$answer_new= new Answer($data);
/*
if ($data->request->original_utterance == "" && $data->session->message_id == 0)
    debug("Пришел чистый запрос. Непонятный старт");
}
else
{
*/
$orig = $data->request->original_utterance;
$opt = $data->request->payload->opt;
$orig = trim($orig);
$orig = strtolower($orig);

switch ($orig)
{
    case 'позвони роботу':
    case 'позвонить роботу':
    case 'попроси умную вику позвонить роботу':
        debug("Звоним роботу");
        $answer_new->txt="Сейчас наберу робота";
        dial("6049");
        break;
    default:
        $answer_new->txt=" . Добро пожаловать! Я помогу тебе управлять домом и вести некоторые личные дела.";
}
header('Content-Type: application/json');
echo json_encode($answer_new->result());

debug ($answer_new->result(),1,"answer_new:");

