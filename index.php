<?php

class Notepad {
	public $file;
	public $notepad_list;
	
	function add($text) {
		if (!isset($this->file))
			return(false);
		$message=$text;
		$message=date("Y-m-d H:i:s").": ".$message;
		file_put_contents($this->file, $message."
", FILE_APPEND);
	return (true);
	}
	
	public function __construct($id) {
		$this->file="notepad/$id.txt";
	}

	function load($full=true,$filter=""){
		$file_array = file($this->file);
		$this->notepad_list=$file_array;
	}
	function get($number=""){
		$result=false;
		$this->load();
		if (strlen()==0)
			$result=$this->notepad_list[count($this->notepad_list)-1];
		return ($result);
	}

}

class User {
	public $user_list;
	public $file="config/users.cfg";

	public function __construct() {
		$this->user_list=unserialize(file_get_contents($this->file));
	}
	
	public function save(){
		file_put_contents($this->file,serialize($this->user_list));
	}
	public function set_name($user_id,$name){
		$this->user_list[$user_id]['name']=$name;
		$this->save();
	}
	
	public function set_place($user_id,$place){
		$this->user_list[$user_id]['place']=$place;
		$this->save();
	}
	

	public function get_name($user_id){
		$name='unknow';
		if (isset($this->user_list[$user_id]['name']))
			$name=$this->user_list[$user_id]['name'];
		else
		{
			$this->user_list[$user_id]['name']=$name;
			$this->save();
		}
		return($name);
	}
	public function get_place($user_id){
		$place='unknow';
		if (isset($this->user_list[$user_id]['place']))
			$place=$this->user_list[$user_id]['place'];
		else
		{
			$this->user_list[$user_id]['place']=$place;
			$this->save();
		}
		return($place);
	}
	
}

class Answer{
	public $text='';
	public $tts='';
	public $response;
	public $data;
	public $button;
	public function add_button($title,$options){
		
	}
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
$user=new User();
$user_name=$user->get_name($data->session->user_id);
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
debug($orig,1,"orig:");
switch ($orig)
{
	case 'позвони роботу':
	case 'позвонить роботу':
	case 'попроси умную вику позвонить роботу':
		debug("Звоним роботу");
		$answer_new->txt="Сейчас наберу робота";
		dial("6049");
		break;
	case 'позвони папе':
	case 'позвонить папе':
	case 'попроси умную вику позвонить папе':
		debug("Звоним роботу");
		$answer_new->txt="Сейчас наберу папе";
		dial("6044");
		break;
	case (preg_match('/.* меня зовут .*/i', $orig) ? true : false) :
		$name=preg_replace('/.* меня зовут /i','',$orig);
		$user->set_name($data->session->user_id,$name);
		$answer_new->txt="Приятно познакомиться ".$name." . Я постараюсь вас запомнить.";
		debug($user->user_list,1,"User_list:");
		break;
	case (preg_match('/как меня зовут.*/i', $orig) ? true : false) :
		$name=$user->get_name($data->session->user_id);
		$answer_new->txt="На сколько я помню, вас зовут ".$name." . Если я не угадала, поправьте меня";
		break;

	case (preg_match('/я сейчас .*/i', $orig) ? true : false) :
		$place=preg_replace('/я сейчас /i','',$orig);
		$user->set_place($data->session->user_id,$place);
		$answer_new->txt="Я поняла что вы сейчас ".$place." . Я постараюсь запомнить это место.";
		debug($user->user_list,1,"User_list:");
		break;

	case (preg_match('/где я.*/i', $orig) ? true : false) :
		$name=$user->get_place($data->session->user_id);
		$answer_new->txt="На сколько я помню, вы сейчас ".$name." . Если я не угадала, поправьте меня";
		break;


	case (preg_match('/как меня зовут.*/i', $orig) ? true : false) :
		$name=$user->get_name($data->session->user_id);
		$answer_new->txt="На сколько я помню, вас зовут ".$name." . Если я не угадала, поправьте меня";
		break;
		
	case (preg_match('/скажи умной вике я ухожу.*/i', $orig) ? true : false) :
	case (preg_match('/^я ушел.*/i', $orig) ? true : false) :
	case (preg_match('/^я ухожу.*/i', $orig) ? true : false) :
		#$name=$user->get_name($data->session->user_id);
		$answer_new->txt="Счастливого пути. Я буду скучать.
		Перед уходом, не забудьте:
			Выключить свет.
			Взять телефон";
		break;

	case (preg_match('/^запиши .*/i', $orig) ? true : false) :
		$notepad=new Notepad($data->session->user_id);
		$text=preg_replace('/запиши /i','',$orig);
		if ($notepad->add($text))
			$answer_new->txt="Я записала: ".$text;
		else
			$answer_new->txt="Извините, почему-то у меня не получилось записать.";
		break;
	case (preg_match('/^прочти последнюю запись.*/i', $orig) ? true : false) :
	case (preg_match('/что записала.*/i', $orig) ? true : false) :
		$notepad=new Notepad($data->session->user_id);
		$result=$notepad->get();
		if ($result==false)
			$answer_new->txt="Я ничего не нашла в записях";
			$answer_new->txt="У меня записано:".$result;
		break;

	case "спасибо":
		$answer_new->txt="Всегда пожалуйста";


	case "помощь":
	case "что ты можешь":
		$answer_new->txt="Привет , ".$user_name."! На данный момент я могу:
		Запомнить как вас зовут и где вы находитесь.
		Позвонить некоторым абонентам.
		Я надеюсь, что со временем я стану умнее.";
		break;
		
	default:
		$answer_new->txt="Привет , ".$user_name."! Я помогу тебе управлять домом и вести некоторые личные дела.";
}
header('Content-Type: application/json');
echo json_encode($answer_new->result());

debug ($answer_new->result(),1,"answer_new:");

