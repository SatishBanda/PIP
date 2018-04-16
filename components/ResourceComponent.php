<?php
/**
 * @author Naveen R
 *
 */

namespace app\components;

use Yii;
use SendGrid;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\Exception;
use yii\helpers\Json;

class ResourceComponent extends Component
{
    /**
     * Sample: ResourceComponent::sendMail('fromMail@example.com', 'toMail@example.com', 'example subject', 'example message');
     */
    public static function sendMail($toMail, $subject, $body, $fromMail = null)
    {
        try {
            $sendGrid = Yii::$app->sendGrid;
            $status = '';

            if (empty($fromMail)) {
                $fromMail = \Yii::$app->params['supportEmail'];
            }

            $message = $sendGrid->compose();
            $status = $message->setFrom($fromMail)
                ->setTo($toMail)
                ->setReplyTo($fromMail)
                ->setSubject($subject)
                ->setHtmlBody($body)
                ->send($sendGrid);
            if (!$status) {
                $error = "Error in mail triggering " . implode(',', array_values($sendGrid->getErrors()));
                throw new Exception(Json::encode($error), 1);
            }
            return $status;
        } catch (Exception $e) {
            throw new Exception(json_encode($e->getMessage()), 1);
        }
    }

    /**
     * Sample: ResourceComponent::sendMailWithTemplate('fromMail@example.com', 'toMail@example.com', 'example subject', 'sample-html', $params);
     */
    public static function sendMailWithTemplate($fromMail = null, $toMail, $subject, $template, $params = null)
    {
        $sendGrid = Yii::$app->sendGrid;
        $status = '';

        if (empty($fromMail)) {
            $fromMail = \Yii::$app->params['supportEmail'];
        }

        if (empty($params)) {
            $params = [];
        }

        $message = $sendGrid->compose($template, $params);
        $status = $message->setFrom($fromMail)
            ->setTo($toMail)
            ->setSubject($subject)
            ->send($sendGrid);

        return $status;
    }

    /**
     * Sample: ResourceComponent::sendMailWithTemplateAndAttachment('fromMail@example.com', 'toMail@example.com', 'example subject', 'sample-html', $params, $attachment);
     */
    public static function sendMailWithTemplateAndAttachment($fromMail = null, $toMail, $subject, $template, $params = null, $attachment)
    {
        $sendGrid = Yii::$app->sendGrid;
        $status = '';

        if (empty($fromMail)) {
            $fromMail = \Yii::$app->params['supportEmail'];
        }

        if (empty($params)) {
            $params = [];
        }

        $message = $sendGrid->compose($template, $params);
        $status = $message->setFrom($fromMail)
            ->setTo($toMail)
            ->setSubject($subject)
            ->attach($attachmentPath)
            ->send($sendGrid);

        return $status;
    }

    /**
     * Sample: ResourceComponent::sendMailWithTemplateAndAttachments('fromMail@example.com', 'toMail@example.com', 'example subject', 'sample-html', $params, $attachments);
     */
    public static function sendMailWithTemplateAndAttachments($fromMail = null, $toMail, $subject, $template, $params = null, $attachments)
    {
        $sendGrid = Yii::$app->sendGrid;
        $status = '';

        if (empty($fromMail)) {
            $fromMail = \Yii::$app->params['supportEmail'];
        }

        if (empty($params)) {
            $params = [];
        }

        $message = $sendGrid->compose($template, $params)
            ->setFrom($fromMail)
            ->setTo($toMail)
            ->setSubject($subject);

        foreach ($attachments as $key => $attachment) {
            $message = $message->attach($attachment);
        }

        $status = $message->send($sendGrid);

        return $status;
    }


    public static function DecodeBase64Image($base64_string, $output_file)
    {

        // open the output file for writing
        $isUploaded = false;
        $ifp = fopen($output_file, 'w');
        chmod($output_file, 0777);
        $data = explode(',', $base64_string);
        if (fwrite($ifp, base64_decode($data[0]))) {
            $isUploaded = true;
        }

        // clean up the file resource
        fclose($ifp);

        return $isUploaded;
    }
}
