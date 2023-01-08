<?php

namespace Wepesi\Mailer;

class smtpConfiguration
{
    private array $config_data;

    public function __construct(){}

    /**
     * @param string $smtp set smtp server name
     * @return smtpConfiguration
     */
    public function smtpServer(string $smtp):smtpConfiguration{
        $this->config_data["smtp_server"] = $smtp;
        return $this;
    }

    /**
     * @param int $port smtp port :default 25 without ssl not recommended
     * @return smtpConfiguration
     */
    public function smtpPort(int $port = 25):smtpConfiguration{
        $this->config_data["smtp_port"] = $port;
        return $this;
    }

    /**
     * @param string $username authentication email account e.g: you@example.com
     * @return smtpConfiguration
     */
    public function setUsername(string $username):smtpConfiguration{
        $this->config_data["username"] = $username;
        return $this;
    }

    /**
     * @param string $password authentication  password account eg: p@ssWord
     * @return smtpConfiguration
     */
    public function setPassword(string $password):smtpConfiguration{
        $this->config_data['password'] = $password;
        return $this;
    }

    /**
     * @param bool $html define if you are going to use html model
     * @return smtpConfiguration
     */
    public function isHTML(bool $html):smtpConfiguration{
        $this->config_data['is_html'] = $html;
        return $this;
    }

    /**
     * @param string $sent_from
     * @return smtpConfiguration
     */
    public function sendFrom(string $sent_from):smtpConfiguration{
        $this->config_data['sendmail_from'] = $sent_from;
        return $this;
    }

    /**
     * @param string $template
     * @return smtpConfiguration
     */
    public function htmlTemplate(string $template) : smtpConfiguration{
        $this->config_data["html_template"] = $template;
        return $this;
    }

    /**
     * @param string $name organization name
     * @return $this
     */
    public function organization(string $name): smtpConfiguration
    {
        $this->config_data['html_template'] = $name;
        return $this;
    }
    public function generate():array{
        return $this->config_data;
    }
}