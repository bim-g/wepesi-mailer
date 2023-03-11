# wepesi-mailer
simple php module to help send email using php mail module
_Note_: Its should test on a live server. from localhost is not yet supported.

```php
use Wepesi\Mailer\Email; 
 use Wepesi\Mailer\smtpConfiguration; 
  
 include_once __DIR__."/../vendor/autoload.php"; 
  
 $smtpConfig = (new smtpConfiguration()) 
     ->smtpServer("localhost") 
     ->smtpPort(25) 
     ->setUsername("you@example.com") 
     ->setPassword("p@ssW0rd") 
     ->organization("Wepesi") 
     ->generate(); 
 $email = new Email($smtpConfig); 
 $email->from("me@example.com"); 
 $email->to("you@example.com","wepesi"); 
 $email->subject("Subject"); 
 $email->text("Hello World"); 
 $email->setBCC(["johndoe@example.com","janedoe@example.com"]); 
 $email->setCC(["alfa@example.com","beta@example.com"]); 
 $result = $email->send(); 
  
 if(isset($result['exception'])){ 
     var_dump("email not sent"); 
 } 
 var_dump($result);
```