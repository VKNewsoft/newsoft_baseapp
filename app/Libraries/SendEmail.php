<?php
namespace App\Libraries;
use \Config\EmailConfig;
use \PHPMailer\PHPMailer\PHPMailer;

class SendEmail
{
	private $email;
	private $config;
	
	public function __construct() {
		$this->config = new EmailConfig;
	}
	
	public function init($exception = true) 
	{
		require_once (APPPATH . 'ThirdParty/PHPMailer/autoload.php');
		
		$this->email = new PHPMailer($exception);
		
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->email->SMTPDebug = 0;
		$this->email->isSMTP();
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->email->Port = 587;
		//Set the encryption system to use - ssl (deprecated) or tls
		$this->email->SMTPSecure = 'tls';
		$this->email->SMTPAuth = true;
		
		$this->setProvider($this->config->provider);
	
		return $this->email;
	}
	
	public function setProvider($provider = 'google') {
		$this->{'set'.ucfirst($provider)}();
	}
	
	private function setGoogle() 
	{
		$this->email->Host = 'smtp.gmail.com';
		$this->email->AuthType = 'XOAUTH2';
		
		require APPPATH . 'ThirdParty/Google-oauth/vendor/autoload.php';

		$provider = new \League\OAuth2\Client\Provider\Google(
			[
				'clientId' => $this->config->client['google']['client_id'],
				'clientSecret' => $this->config->client['google']['client_secret'],
			]
		);

		$this->email->setOAuth(
			new \PHPMailer\PHPMailer\OAuth(
				[
					'provider' => $provider,
					'clientId' => $this->config->client['google']['client_id'],
					'clientSecret' => $this->config->client['google']['client_secret'],
					'refreshToken' => $this->config->client['google']['refresh_token'],
					'userName' => $this->config->from,
				]
			)
		);
	}
	
	private function setStandard() 
	{
		$this->email->Host = $this->config->client['standard']['host'];
		$this->email->Username = $this->config->client['standard']['username'];
		$this->email->Password = $this->config->client['standard']['password'];
	}
	
	private function setAmazonSES() 
	{
		$this->email->Username = $this->config->client['ses']['username'];
		$this->email->Password = $this->config->client['ses']['password'];
		
		// Specify a configuration set. If you do not want to use a configuration
		// set, comment or remove the next line.
		// $mail->addCustomHeader('X-SES-CONFIGURATION-SET', 'ConfigSet');
		
		// If you're using Amazon SES in a region other than US West (Oregon), 
		// replace email-smtp.us-west-2.amazonaws.com with the Amazon SES SMTP  
		// endpoint in the appropriate region.
		$this->email->Host = 'email-smtp.us-east-1.amazonaws.com';
	}
	
	public function send($data) 
	{ 
		try {
			$this->email->CharSet = 'utf-8';
			$this->email->setFrom($data['from_email'], $data['from_title']);
			$this->email->addAddress($data['to_email'], $data['to_name']);
			$this->email->Subject = $data['email_subject'];
		
			if (!empty($data['images'])) {
				foreach($data['images'] as $name => $path) {
					$this->email->AddEmbeddedImage($path, $name);
				}
			}
			
			if (!empty($data['attachment'])) {
				$this->email->AddAttachment($data['attachment']['path'], $data['attachment']['name']);
			}
			
			$this->email->msgHTML($data['email_content']);

			if ($this->email->send())
				return ['status' => 'ok', 'message' => 'Email berhasil dikirim'];

			return ['status' => 'error', 'message' => 'Failed to send email'];
			
			// $this->email->AltBody = 'This is a plain-text message body';
		}
		catch (\PHPMailer\PHPMailer\Exception $e) {
			// echo $e->errorMessage(); die;
			return ['status' => 'error', 'message' => $e->errorMessage()];
		}
		catch (\Exception $e) {
			// echo $e->getMessage(); die;
			return ['status' => 'error', 'message' => $e->getMessage()];
		}
	}
}