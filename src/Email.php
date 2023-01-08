<?php

namespace Wepesi\Mailer;

use Wepesi\App\Schema;
use Wepesi\App\Validate;
use Wepesi\Resolver\Option;
use Wepesi\Resolver\OptionsResolver;

class Email
{
    private string $smtp_server;
    private string $smtp_port;
    private string $username;
    private string $password;
    private string $reply_to;
    private array $config;
    /**
     * @var array|string[]
     */
    private array $data_option_resolver;
    /**
     * @var Validate
     */
    private Validate $validate;
    private Schema $schema;
    const CONTENT_TYPE_TEXT = "text/plain";
    const CONTENT_TYPE_HTML = "text/html";
    private string $content_type;

    public function __construct()
    {
        $this->data_option_resolver = ['smtp_server', 'smtp_port', 'username', 'password'];
        $this->validate = new Validate();
        $this->schema = new Schema();
        $this->content_type = self::CONTENT_TYPE_TEXT;
    }

    /**
     * @param array $smtp_config
     * @return array|true
     */
    public function applyConfig(array $smtp_config)
    {
        try{
            $this->config = $smtp_config;
            $schema = [];
            foreach ($smtp_config as $item => $value) {
                if (in_array($item, $this->data_option_resolver)) continue;
                $this->data_option_resolver[] = new Option($item);
                $schema[$item] = $this->schema->any();
            }
            $resolver = new OptionsResolver($this->data_option_resolver);
            $options = $resolver->resolve($smtp_config);
            if(isset($options['exception'])){
                throw new \Exception($options['exception']);
            }
            // validate input data
            $schema ['smtp_server'] = $this->schema->string()->required()->generate();
            $schema ['smtp_port'] = $this->schema->number()->positive()->min(25)->required()->generate();
            $schema ['username'] = $this->schema->string()->min(5)->email()->required()->generate();
            $schema ['password'] = $this->schema->string()->min(5)->required()->generate();

            $this->validate->check($this->data_option_resolver,$schema);
            if(!$this->validate->passed()){
                throw new \Exception($this->validate->errors());
            }

            $this->smtp_server = $this->config['smtp_server'];
            $this->smtp_port = $this->config['smtp_port'];
            $this->username = $this->config['username'];
            $this->password = $this->config['password'];
            $this->reply_to = $this->config['reply_to'];

            return true;
        }catch(\Exception $ex){
            return ['exception' => $ex->getMessage()];
        }
    }

    /**
     * @param array $cc_contact
     * @return void
     */
    //TODO implement send CC contact
    public function setCC(array $cc_contact)
    {
        if (count($cc_contact) > 0) {
            $this->config['email_cc'] = implode(';', $cc_contact);
        }
    }
    /**
     * @param array $bcc_contact
     * @return void
     */
    //TODO implement send BCC contact
    public function setBCC(array $bcc_contact)
    {
        if (count($bcc_contact) > 0) {
            $this->config['email_bcc'] = implode(';', $bcc_contact);
        }
    }

    /**
     * @param string $sendmail_from
     * @return void
     */
    public function from(string $sendmail_from)
    {
        $this->config["email_from"] = trim($sendmail_from);
    }
    //TODO implement reply_to module

    /**
     * @param string $reply_to
     * @return void
     */
    public function replyTo(string $reply_to)
    {
        if (strlen(trim($reply_to))) $this->config['reply_to'] = trim($reply_to);
    }

    /**
     * @param string $email_to
     * @param string|null $name
     * @return void
     */
    public function to(string $email_to,?string $name=null)
    {
        if (strlen(trim($email_to))) {
            $this->config["email_to"] = $email_to;
            if($name && strlen(trim($name))>0) $this->config['email_to'] = "$name <$email_to>";
        }
    }

    /**
     * @param array $email_to
     * @return void
     */
    public function toMultiple(array $email_to){
        if(count($email_to)>0) $this->config['email_to'] = implode(";",$email_to);
    }

    /**
     * @param string $email_subject
     * @return void
     */
    public function subject(string $email_subject)
    {
        if (strlen(trim($email_subject))) $this->config['email_subject'] = $email_subject;
    }

    /**
     * @param string $message_text
     * @return void
     */
    public function text(string $message_text)
    {
        if (strlen(trim($message_text))) $this->config['email_message'] = $message_text;
    }

    /**
     * @return array
     */
    public function send(): array
    {
        try {
            $result = [
                'status' => 1,
                'exception' => 'failed send email'
            ];
            // validate mail configuration
            $schema = [
                "email_from" => $this->schema->string()->email()->min(6)->required()->generate(),
                "email_to" => $this->schema->string()->email()->min(6)->required()->generate(),
                "email_subject" => $this->schema->string()->min(3)->max(70)->required()->generate(),
                "email_message" => $this->schema->string()->min(1)->required()->generate(),
            ];
            //
            if(isset($this->config['email_cc'])) $schema["email_cc"] = $this->schema->array()->min(1)->required()->generate();
            if(isset($this->config['email_bcc'])) $schema["email_bcc"] = $this->schema->array()->min(1)->required()->generate();
            if(isset($this->config['organization'])) $schema["organization"] = $this->schema->string()->min(3)->required()->generate();
            if(isset($this->config['isHtml'])) $schema["isHtml"] = $this->schema->any();

            $this->validate->check($this->config,$schema);

            if(!$this->validate->passed()) throw new \Exception($this->validate->errors());
            //content type
            if(isset($this->config["isHtml"]) && $this->config['isHtml']) $this->content_type = self::CONTENT_TYPE_HTML;
            //
            $from = $this->config['email_from'];
            $to = $this->config['email_to'];
            $subject = $this->config['email_subject'];
            $message = $this->config['email_message'];
            $cc = $this->config['email_cc'] ?? false;
            $bcc = $this->config['email_bcc'] ?? false;
            $organization = $this->config['organization'] ?? false;
            // Header configuration
            $header[] = ["Reply-To" => $this->reply_to];
            $header[] = ["Return-Path" => $from];
            $header[] = ["From" => $from];

            if($cc) $header[] = ["CC" => $cc];
            if($bcc) $header[] = ["BCC" => $bcc];
            if($organization) $header[] = ["Organization" => $organization];

            $header[] = ["MIME-Version" => 1.0];
            $header[] = ["Content-type" => "$this->content_type; charset=iso-8859-1"];
            $header[] = ["X-Priority" => 3];
            $header[] = ["X-Mailer" => "PHP/" . phpversion() ];

            //set email configurations
            ini_set('SMTP', $this->smtp_server);
            ini_set('smtp_port', $this->smtp_port);
            ini_set('username', $this->username);
            ini_set('password', $this->password);
            ini_set('sendmail_from', $from);

            // send email
            $send = mail($to, $subject, $message, $header);
            if ($send) {
                $result = [
                    "status" => 0,
                    "exception" => "email sent successfully"
                ];
            }
            return $result;
        } catch (\Exception $ex) {
            return [
                'status' => 1,
                'exception' => $ex->getMessage()
            ];
        }
    }

    protected function getError(){
        //TODO implement error
    }
}