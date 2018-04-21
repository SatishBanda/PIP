<?php

/**
 * @author Naveen R & satish
 *
 */

namespace app\components;

use app\models\SettingMaster;
use Yii;
use yii\base\Component;
use app\models\EmailTemplates;
use app\models\Clients;
use app\models\CompanyUsers;
use app\models\Brands;
use yii\db\Exception;
use yii\helpers\Json;
use yii\helpers\Url;

class MailComponent extends Component
{

    const DEFAULT_EMAIL = 'admin@pip.com';
    const DEFAULT_NUMBER = '111-111-11';

    /**
     * Send password reset mail
     */
    public static function sendPasswordResetTokenMail($user)
    {

        $status = '';
        $link = \Yii::$app->params['frontendURL'] . '#/set-password?token=' . $user->password_reset_token;
        // get forgot password mail
        $forgot_mail_templates = \app\models\EmailTemplates::find()->where([
            'template_id' => 1
        ])->One();

        if (!empty($forgot_mail_templates)) {
            // removing place holders in mail body
            $firstName = self::getFirstName($user);
            $body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $forgot_mail_templates->body);
            $response = self::replaceDynamicVariables($body, $user);
            $body = $response['body'];
            $brand = $response['brand'];
            $body = str_replace("&lt;&lt;password_btn&gt;&gt;", '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnButtonBlock" style="min-width:100%;">
			    <tbody class="mcnButtonBlockOuter">
			        <tr>
			            <td style="padding-top:0; padding-right:18px; padding-bottom:18px; padding-left:18px;" valign="top" align="center" class="mcnButtonBlockInner">
			                <table border="0" cellpadding="0" cellspacing="0" class="mcnButtonContentContainer" style="border-collapse: separate !important;border-radius: 3px;background-color: #0076BC;">
			                    <tbody>
			                        <tr>
			                            <td align="center" valign="middle" class="mcnButtonContent" style="font-family: Arial; font-size: 16px; padding: 15px;">
			                                <a class="mcnButton " title="Reset Your Password" href="' . $link . '" target="_blank" style="font-weight: bold;letter-spacing: normal;line-height: 100%;text-align: center;text-decoration: none;color: #FFFFFF;">Reset Your Password</a>
			                            </td>
			                        </tr>
			                    </tbody>
			                </table>
			            </td>
			        </tr>
			    </tbody>
			</table>', $body);

            $status = ResourceComponent::sendMail($brand['support_email'], $user->username, $forgot_mail_templates->subject, $body);
        }
        return $status;
    }

    /**
     * [replaceDynamicVariables description]
     * @method replaceDynamicVariables
     * @param  [type]          $body [description]
     * @return [type]                [description]
     */
    public static function replaceDynamicVariables($body, $user)
    {

        $response = self::replaceEmailAndPhone($body, $user);
        $body = $response['body'];
        $return['brand'] = $response['brand'];
        $doc = new \DOMDocument();
        $doc->loadHTML($body);
        $tags = $doc->getElementsByTagName('img');
        foreach ($tags as $tag) {
            $old_src = $tag->getAttribute('src');
            $new_src_url = Yii::$app->params['frontendURL'] . $old_src;
            if ($return['brand'] && $return['brand']['brand_logo']) {
                $new_src_url = Url::base(true) . '/images/uploads/brands/' . $return['brand']['brand_logo'];
            }
            $tag->setAttribute('src', $new_src_url);
        }
        $body = $doc->saveHTML();
        $return['body'] = $body;

        return $return;
    }

    /**
     * Send mail to verify the user account on email change
     */
    public static function sendUserActivateMail($user)
    {

        $status = '';
        $link = \Yii::$app->params['frontendURL'] . '#/activate-user?token=' . $user->password_reset_token;
        // get forgot password mail
        $verify_account_mail_template = \app\models\EmailTemplates::find()->where([
            'template_id' => 2
        ])->One();

        if (!empty($verify_account_mail_template)) {
            // removing place holders in mail body
            $firstName = self::getFirstName($user);
            $body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $verify_account_mail_template->body);
            $response = self::replaceDynamicVariables($body, $user);
            $body = $response['body'];
            $brand = $response['brand'];
            $body = str_replace("&lt;&lt;password_btn&gt;&gt;", '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnButtonBlock" style="min-width:100%;">
			    <tbody class="mcnButtonBlockOuter">
			        <tr>
			            <td style="padding-top:0; padding-right:18px; padding-bottom:18px; padding-left:18px;" valign="top" align="center" class="mcnButtonBlockInner">
			                <table border="0" cellpadding="0" cellspacing="0" class="mcnButtonContentContainer" style="border-collapse: separate !important;border-radius: 3px;background-color: #0076BC;">
			                    <tbody>
			                        <tr>
			                            <td align="center" valign="middle" class="mcnButtonContent" style="font-family: Arial; font-size: 16px; padding: 15px;">
			                                <a class="mcnButton " title="Verify Your Email" href="' . $link . '" target="_blank" style="font-weight: bold;letter-spacing: normal;line-height: 100%;text-align: center;text-decoration: none;color: #FFFFFF;">Verify Your Email</a>
			                            </td>
			                        </tr>
			                    </tbody>
			                </table>
			            </td>
			        </tr>
			    </tbody>
			</table>', $body);
            $status = ResourceComponent::sendMail($brand['support_email'], $user->username, $verify_account_mail_template->subject, $body);
        }
        return $status;
    }

    /**
     * Send password set mail when user is newly created by the admin
     */
    public static function sendPasswordSetTokenMail($user)
    {
        $status = '';
        $link = \Yii::$app->params['frontendURL'] . '#/set-password?token=' . $user->password_reset_token;
        // get forgot password mail
        $set_pwd_mail_template = \app\models\EmailTemplates::find()->where([
            'template_id' => 3
        ])->One();

        if (!empty($set_pwd_mail_template)) {
            // removing place holders in mail body
            $firstName = self::getFirstName($user);
            $body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $set_pwd_mail_template->body);
            $response = self::replaceDynamicVariables($body, $user);
            $body = $response['body'];
            $brand = $response['brand'];
            $body = str_replace("&lt;&lt;password_btn&gt;&gt;", '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnButtonBlock" style="min-width:100%;">
			    <tbody class="mcnButtonBlockOuter">
			        <tr>
			            <td style="padding-top:0; padding-right:18px; padding-bottom:18px; padding-left:18px;" valign="top" align="center" class="mcnButtonBlockInner">
			                <table border="0" cellpadding="0" cellspacing="0" class="mcnButtonContentContainer" style="border-collapse: separate !important;border-radius: 3px;background-color: #0076BC;">
			                    <tbody>
			                        <tr>
			                            <td align="center" valign="middle" class="mcnButtonContent" style="font-family: Arial; font-size: 16px; padding: 15px;">
			                                <a class="mcnButton " title="Set Your Password" href="' . $link . '" target="_blank" style="font-weight: bold;letter-spacing: normal;line-height: 100%;text-align: center;text-decoration: none;color: #FFFFFF;">Set Your Password</a>
			                            </td>
			                        </tr>
			                    </tbody>
			                </table>
			            </td>
			        </tr>
			    </tbody>
			</table>', $body);

            $status = ResourceComponent::sendMail($user->username, $set_pwd_mail_template->subject, $body);
        }
        return $status;
    }


    public static function sendMailToCandidate($email)
    {
        try {

            $settings = SettingMaster::getSetting();
            if ($email && $settings) {
                $sendGrid = Yii::$app->sendGrid;
                $fromMail = self::DEFAULT_EMAIL;
                if (!empty($settings)) {
                    $fromMail = $settings->default_email_for_outgoing;
                }
                $message = $sendGrid->compose();
                $status = $message->setFrom($fromMail)
                    ->setTo($email->send_to)
                    //->setReplyTo($fromMail)
                    ->setSubject($email->subject)
                    ->setHtmlBody($email->body)
                    ->send($sendGrid);
                if (!$status) {
                    $error = "Error in mail triggering " . implode(',', array_values($sendGrid->getErrors()));
                    throw new Exception(Json::encode($error), 1);
                }

                return $status;
            }


        } catch (Exception $e) {
            throw new \yii\base\Exception($e->getMessage());
        }

    }

}
