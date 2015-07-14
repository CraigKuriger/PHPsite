<?php
require 'vendor/autoload.php';
date_default_timezone_set('America/New_York');

// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;

// $log = new Logger('name');
// $log->pushHandler(new StreamHandler('app.log', Logger::WARNING));
// $log->addWarning('Oh Noes.');

$app = new \Slim\Slim( array(
	'view' => new \Slim\Views\Twig()
));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->get('/', function() use($app){
	$app->render('index.twig');
})->name('home');

$app->get('/contact', function() use($app){
	$app->render('contact.twig');
})->name('contact');

$app->post('/contact', function() use($app){
	$name = $app->request->post('name');
	$email = $app->request->post('email');
	$message = $app->request->post('message');
	if (!empty($name) && !empty($email) && !empty($message)){
		$cleanName = filter_var($name, FILTER_SANITIZE_STRING);
		$cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
		$cleanMessage = filter_var($message, FILTER_SANITIZE_STRING);
	} else {
		$app->redirect('/contact');
	}

	$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
	$mailer = \Swift_Mailer::newInstance($transport);
	$message = \Swift_Message::newInstance();
	$message->setSubject('Email from the website');
	$message->setFrom(array(
		$cleanEmail => $cleanName
		));
	$message->setTo(array('craig.kuriger@icloud.com'));
	$message->setBody($cleanMessage);
	$result = $mailer->send($message);
	if($result > 0){
		$app->redirect('/');
	} else {
		$app->redirect('/contact');
	}
});


$app->run();