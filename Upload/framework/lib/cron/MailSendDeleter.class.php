<?php


/**
 * Sendet E-Mailnachrichten
 * @author Christian
 *
 */

class MailSendDeleter extends AbstractCron
{

    private $result = null;

    public function __construct()
    {

    }

    public function execute()
    {
        $grenze = time() - 14 * 24 * 60 * 60;       // 14 vorher

        DB::getDB()->query("DELETE FROM mail_send WHERE mailSent > 0 && mailSent < $grenze");

    }

    public function getName()
    {
        return "Versendete Mails löschen";
    }

    public function getDescription()
    {
        return "Mails, die verschickt sind und älter als 14 Tage sind, werden gelöscht.";
    }

    /**
     *
     *
     * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
     */
    public function getCronResult()
    {
        return ['success' => true, 'resultText' => "Datensätze gelöscht."];
    }

    public function informAdminIfFail()
    {
        return false;
    }

    public function executeEveryXSeconds()
    {
        return 86400;        // Einmal alle 24 Stunden ausführen.
    }
}


?>