<?php

class DichVuEmail
{
    public function gui($to, $subject, $body)
    {
        $driver = defined('MAIL_DRIVER') ? strtolower(MAIL_DRIVER) : 'mail';
        if ($driver === 'smtp') {
            return $this->guiSmtp($to, $subject, $body);
        }

        $headers = $this->taoHeaders();
        return @mail($to, $subject, $body, $headers);
    }

    private function guiSmtp($to, $subject, $body)
    {
        $host = MAIL_HOST;
        $port = (int)MAIL_PORT;
        $encryption = strtolower(MAIL_ENCRYPTION);
        $target = ($encryption === 'ssl' ? 'ssl://' : '') . $host;

        $fp = @fsockopen($target, $port, $errno, $errstr, 20);
        if (!$fp) {
            return false;
        }

        stream_set_timeout($fp, 20);

        if (!$this->docPhanHoi($fp, array(220))) {
            fclose($fp);
            return false;
        }

        $hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        if (!$this->lenh($fp, 'EHLO ' . $hostname, array(250))) {
            fclose($fp);
            return false;
        }

        if ($encryption === 'tls') {
            if (!$this->lenh($fp, 'STARTTLS', array(220))) {
                fclose($fp);
                return false;
            }
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                return false;
            }
            if (!$this->lenh($fp, 'EHLO ' . $hostname, array(250))) {
                fclose($fp);
                return false;
            }
        }

        if (MAIL_USERNAME !== '' || MAIL_PASSWORD !== '') {
            if (!$this->lenh($fp, 'AUTH LOGIN', array(334))) {
                fclose($fp);
                return false;
            }
            if (!$this->lenh($fp, base64_encode(MAIL_USERNAME), array(334))) {
                fclose($fp);
                return false;
            }
            if (!$this->lenh($fp, base64_encode(MAIL_PASSWORD), array(235))) {
                fclose($fp);
                return false;
            }
        }

        $fromEmail = MAIL_FROM !== '' ? MAIL_FROM : 'no-reply@buffet-chay.local';
        if (!$this->lenh($fp, 'MAIL FROM:<' . $fromEmail . '>', array(250))) {
            fclose($fp);
            return false;
        }
        if (!$this->lenh($fp, 'RCPT TO:<' . $to . '>', array(250, 251))) {
            fclose($fp);
            return false;
        }
        if (!$this->lenh($fp, 'DATA', array(354))) {
            fclose($fp);
            return false;
        }

        $message = $this->taoNoiDungEmail($to, $subject, $body);
        fwrite($fp, $this->escapeData($message) . "\r\n.\r\n");
        if (!$this->docPhanHoi($fp, array(250))) {
            fclose($fp);
            return false;
        }

        $this->lenh($fp, 'QUIT', array(221));
        fclose($fp);
        return true;
    }

    private function taoHeaders()
    {
        $fromName = MAIL_FROM_NAME !== '' ? MAIL_FROM_NAME : APP_NAME;
        $fromEmail = MAIL_FROM !== '' ? MAIL_FROM : 'no-reply@buffet-chay.local';
        return "From: " . $fromName . " <" . $fromEmail . ">\r\n"
            . "Reply-To: " . $fromEmail . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n";
    }

    private function taoNoiDungEmail($to, $subject, $body)
    {
        $fromName = MAIL_FROM_NAME !== '' ? MAIL_FROM_NAME : APP_NAME;
        $fromEmail = MAIL_FROM !== '' ? MAIL_FROM : 'no-reply@buffet-chay.local';
        $headers = array(
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'To: <' . $to . '>',
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit'
        );
        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private function escapeData($message)
    {
        $message = str_replace(array("\r\n", "\r"), "\n", $message);
        $lines = explode("\n", $message);
        foreach ($lines as $i => $line) {
            if (isset($line[0]) && $line[0] === '.') {
                $lines[$i] = '.' . $line;
            }
        }
        return implode("\r\n", $lines);
    }

    private function lenh($fp, $command, $expected)
    {
        fwrite($fp, $command . "\r\n");
        return $this->docPhanHoi($fp, $expected);
    }

    private function docPhanHoi($fp, $expected)
    {
        $code = 0;
        while (($line = fgets($fp, 515)) !== false) {
            if (strlen($line) >= 3) {
                $code = (int)substr($line, 0, 3);
            }
            if (strlen($line) < 4 || substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return in_array($code, $expected, true);
    }
}
