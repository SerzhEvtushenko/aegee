<?php
/**
 * @package SolveProject
 * @subpackage Mailer
 *
 * @author Dmitriy Plish <d.plish@gmail.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 10.11.10 14:05
 */

/**
 * Works with swift mailer
 *
 * @version 1.0
 *
 * @author Dmitriy Plish <d.plish@gmail.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slMailer {

    protected static $_mailer       = null;
    protected static $_transport    = null;
    protected static $_config       = null;


    protected static $emailsToSkip = array();


    const STATUS_SENT = 1;
    const STATUS_UNSENT = 0;
    const STATUS_ERROR_MSG = 3;

    /**
     * Sending mail
     * @static
     * @param string $msgTo адрес или array('email'=>'name') получателя(-лей)
     * @param string $msgSubject email subject
     * @param string $msgBody email body
     * @param bool $from email from
     * @param int $priority
     * @param bool $cc
     * @param bool $bcc
     * @return int
     */
    public static function sendMail( $msgTo = '', $msgSubject = '', $msgBody = '', $from = false, $priority = 1, $cc = false, $bcc = false ) {
        if (strcmp(trim($msgTo),'import@roshen.ua') === 0) {
            SL::log('Cancelled mail to import@roshen.ua: '."\n".dumpAsString(func_get_args())."\n", 'mail');
            return 0;
        }

	    $from = strlen($from) ? $from : SL::getProjectConfig('email');

        if (is_string($msgTo) && strpos($msgTo, ',') !== false) {
            $msgTo = explode(',', $msgTo);
        }

        require_once dirname(__FILE__) . '/../../libs/Swift-4.1.4/swift_required.php';
        try {

            $message    = self::_getMessage($msgTo, $msgSubject, $msgBody, $priority, $from, $cc, $bcc);
            $result     = self::_send($message);

            if (class_exists('MailLog')) {
                $m = new MailLog();
                $m->email = is_array($msgTo) ? implode(',', $msgTo) : $msgTo;
                $m->subject = $msgSubject;
                $m->body = $msgBody;
                $m->save();
            }
        } catch( Exception $e ) {
            SL::log($e->getMessage(), 'mail');
            $result = 0;
        }
        return $result;
    }


    /**
     * @static
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param int  $priority
     * @param bool $from
     * @param bool $cc
     * @param bool $bcc
     * @return Swift_Mime_Message
     * Создает Swift_Mime_Message
     * Проверка входящих параметров не осуществляется ибо Swift_Mime_Message сам кинет Exception в случае несоответствия
     */
    protected static function _getMessage( $to = '', $subject = '', $body = '', $priority = 1, $from = false, $cc = false, $bcc = false ) {

        try {
            $body       = strval( $body );
            $subject    = strval( $subject );
            $priority   = abs(intval($priority));

            $message  = Swift_Message::newInstance();

            if( FALSE === $from ) {
//                $from = array( 'no-reply@'.$_SERVER['HTTP_HOST'] => $_SERVER['HTTP_HOST'] );
                $from = SL::getProjectConfig('mailing/from');
            }

            $message->setFrom( $from );
            $message->setSubject( $subject );

            $pattern = "/(src|background)=\"(.*)\"/Ui";
            preg_match_all("/(src|background)=\"(.*)\"/Ui", $body, $images);
            if(isset($images[2])) {
                foreach($images[2] as $i => $url) {
                    if (isset($_SERVER['SERVER_NAME']) && strrpos($url, $_SERVER['SERVER_NAME'])) {
                        $cid = $message->embed(Swift_Image::fromPath($url));
                        $body = preg_replace("/".$images[1][$i]."=\"".preg_quote($url, '/')."\"/Ui", $images[1][$i]."=\"".$cid."\"", $body);
                    }
                }
            }
            $message->setBody( $body, 'text/html' );

            $message->setTo( $to );
            if( $cc ) {
                $message->setCc( $cc );
            }
            if( $bcc ) {
                $message->setBcc($bcc);
            }
        } catch( Exception $e ) {
            throw $e;
        }

        return $message;
    }


    /**
     * Internal function for sending email
     * @param $message Swift_Message
     * @return int result
     */
    protected static function _send( $message ) {
        $mailer = self::getMailer();
        try {
            $result = $mailer->send($message);
        } catch (Exception $e) {
            $result = 0;
        }

        return $result > 1 ? 1 : $result;
    }



    /**
     * @static
     * @return Swift_Transport
     */
    public static function getTransport( ) {
        if( !is_null(self::$_transport)) {
            $transport = self::$_transport;
        } else {
            $config = self::_getConfig();

            switch( $config['transport'] ) {
                case 'sendmail':
                    if (isset($config['params'])) {
                        $transport = Swift_SendmailTransport::newInstance($config['params']);
                    } else {
                        $transport = Swift_SendmailTransport::newInstance();
                    }
                    break;
                case 'smtp':
                    $transport = Swift_SmtpTransport::newInstance( $config['host'], $config['port']);
                    if( $config['login'] ) {
                        $transport->setUsername($config['login']);
                        if( $config['password'] ) {
                            $transport->setPassword($config['password']);
                        }
                    }
                    break;
                case 'mail':
                default:
                    $transport = Swift_MailTransport::newInstance();
                    break;


            }
            self::$_transport = $transport;
        }
        return $transport;
    }


    /**
     * @static
     * @return Swift_Mailer::newInstance
     */
    public static function getMailer( ) {
        if( !is_null(self::$_mailer)) {
            $mailer = self::$_mailer;
        } else {
            $transport = self::getTransport();
            $mailer = Swift_Mailer::newInstance($transport);
            self::$_mailer = $mailer;
        }
        return $mailer;
    }



    protected static function _getConfig() {
        if( is_null(self::$_config) ) {
            self::$_config = SL::getProjectConfig('mailing');
        }
        return self::$_config;
    }

}
