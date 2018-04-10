<?php

/**
 * @author Naveen R & satish
 *
 */

namespace app\components;

use Yii;
use yii\base\Component;
use app\models\EmailTemplates;
use app\models\Clients;
use app\models\CompanyUsers;
use app\models\Brands;
use yii\helpers\Url;
class MailComponent extends Component
{

  const DEFAULT_EMAIL= 'sky@analytics.com';
  const DEFAULT_NUMBER = '899-989-89898';

    /**
     * Send password reset mail
     */
    public static function sendPasswordResetTokenMail($user)
    {
        /* return \Yii::$app
            ->mailer
            ->compose(
                ['html' => 'password-reset-token-html', 'text' => 'password-reset-token-text'],
            	[
            		'user' => $user,
            		'appName' =>  \Yii::$app->name,
            		'resetURL' => \Yii::$app->params['frontendURL'].'#/set-password?token='.$user->password_reset_token
                ]
            )
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name])
            ->setTo($user->username)
            ->setSubject('Password reset for ' . \Yii::$app->name)
            ->send(); */
        // 		$params = [
        // 				'user' => $user,
        // 				'appName' =>  Yii::$app->name,
        // 				'resetURL' => Yii::$app->params['frontendURL'].'#/set-password?token='.$user->password_reset_token
        // 		];
        // 		return ResourceComponent::sendMailWithTemplate($brand['support_email'], $user->username, 'Password reset for ' . Yii::$app->name, 'password-reset-token-html',$params);

        $status = '';
        $link = \Yii::$app->params['frontendURL'].'#/set-password?token='.$user->password_reset_token;
        // get forgot password mail
        $forgot_mail_templates = \app\models\EmailTemplates::find()->where([
                'template_id' => 1
        ])->One();

        if (! empty($forgot_mail_templates)) {
            // removing place holders in mail body
            $firstName = self::getFirstName($user);
            $body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $forgot_mail_templates->body);
            $response = self::replaceDynamicVariables($body,$user);
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

            $status  = ResourceComponent::sendMail($brand['support_email'], $user->username, $forgot_mail_templates->subject, $body);
        }
        return $status;
    }
    /**
     * [replaceDynamicVariables description]
     * @method replaceDynamicVariables
     * @param  [type]          $body [description]
     * @return [type]                [description]
     */
    public static function replaceDynamicVariables($body,$user){

       $response = self::replaceEmailAndPhone($body,$user);
       $body = $response['body'];
       $return['brand'] = $response['brand'];
       $doc = new \DOMDocument();
       $doc->loadHTML($body);
       $tags = $doc->getElementsByTagName('img');
      foreach($tags as $tag){
          $old_src = $tag->getAttribute('src');
          $new_src_url = Yii::$app->params['frontendURL'].$old_src;
          if($return['brand'] && $return['brand']['brand_logo']){
            $new_src_url = Url::base(true).'/images/uploads/brands/'.$return['brand']['brand_logo'];
          }
          $tag->setAttribute('src', $new_src_url);
        }
       $body =  $doc->saveHTML();
       $return['body'] = $body;

       return $return;
    }

    /**
     * Send mail to verify the user account on email change
     */
    public static function sendUserActivateMail($user)
    {
        /* $params = [
         'user' => $user,
         'appName' =>  Yii::$app->name,
         'activateURL' => Yii::$app->params['frontendURL'].'#/activate-user?token='.$user->password_reset_token
         ];
         return ResourceComponent::sendMailWithTemplate(\Yii::$app->params['supportEmail'], $user->username, 'Activate User for ' . Yii::$app->name, 'activate-user-html',$params);
         */

        $status = '';
        $link = \Yii::$app->params['frontendURL'].'#/activate-user?token='.$user->password_reset_token;
        // get forgot password mail
        $verify_account_mail_template = \app\models\EmailTemplates::find()->where([
                'template_id' => 2
        ])->One();

        if (! empty($verify_account_mail_template)) {
            // removing place holders in mail body
            $firstName = self::getFirstName($user);
            $body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $verify_account_mail_template->body);
            $response = self::replaceDynamicVariables($body,$user);
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
            $status  = ResourceComponent::sendMail($brand['support_email'], $user->username, $verify_account_mail_template->subject, $body);
        }
        return $status;
    }

    /**
     * Send password set mail when user is newly created by the admin
     */
    public static function sendPasswordSetTokenMail($user)
    {
        $status = '';
        $link = \Yii::$app->params['frontendURL'].'#/set-password?token='.$user->password_reset_token;
        // get forgot password mail
        $set_pwd_mail_template = \app\models\EmailTemplates::find()->where([
                'template_id' => 3
        ])->One();

        if (! empty($set_pwd_mail_template)) {
            // removing place holders in mail body
            $firstName = self::getFirstName($user);
            $body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $set_pwd_mail_template->body);
            $response = self::replaceDynamicVariables($body,$user);
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

            $status  = ResourceComponent::sendMail($brand['support_email'], $user->username, $set_pwd_mail_template->subject, $body);
        }
        return $status;
    }

    /**
     * Inform about the newly created company when already existing client created again by the admin
     */
    public static function sendNewCompanyCreationMail($user)
    {
        $status = '';
        $clientCreationMail = EmailTemplates::find()
            ->where(['template_id' => 6])
            ->One();

        if (!empty($clientCreationMail)) {
            // removing place holders in mail body
            $firstName = self::getFirstName($user);
            $body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $clientCreationMail->body);
            $response = self::replaceDynamicVariables($body,$user);
            $body = $response['body'];
            $brand = $response['brand'];
            $status  = ResourceComponent::sendMail($brand['support_email'], $user->username, $clientCreationMail->subject, $body);
        }
        return $status;
    }
    /**
     * Send mail for a new message in upload file chat history
     */
    public static function sendFileHistoryChatUpdates($sender_type, $to_email, $sender_details = null, $msg_type = null, $file_link=null)
    {
        $body = '
        <table style="height: 100%; width: 76.378%; margin-left: auto; margin-right: auto;" border="0" cellspacing="0" cellpadding="0" align="center">
        	<tbody>
        		<tr>
        			<td style="vertical-align: top; width: 100%;">
        				<table style="width: 100%;" border="0" cellspacing="0"
        					cellpadding="0">
        					<tbody>
        						<tr>
        							<td style="vertical-align: top; width: 1008px;">
        								<table style="width: 100%;" border="0" cellspacing="0"
        									cellpadding="0">
        									<tbody>
        										<tr>
        											<td style="vertical-align: top; text-align: center;">&nbsp;</td>
        										</tr>
        									</tbody>
        								</table>
        							</td>
        						</tr>
        						<tr style="text-align: center;">
        							<td style="vertical-align: top; width: 1008px;">
        								<table style="width: 100%;" border="0" cellspacing="0"
        									cellpadding="0">
        									<tbody>
        										<tr>
        											<td style="vertical-align: top;">
        												<table style="width: 100%;" border="0" cellspacing="0"
        													cellpadding="0" align="left">
        													<tbody>
        														<tr>
        															<td style="vertical-align: top;">
        																<p>
        																	<img
        																		style="height: 76px; width: 341px; display: block; margin-left: auto; margin-right: auto;"
        																		src="assets/images/ACA-Reporting-Logo.png" alt="" />
        																</p>
        															</td>
        														</tr>
        													</tbody>
        												</table>
        											</td>
        										</tr>
        									</tbody>
        								</table>
        							</td>
        						</tr>
        						<tr style="text-align: center;">
        							<td style="vertical-align: top; width: 1008px;">
        								<table style="width: 100%;" border="0" cellspacing="0"
        									cellpadding="0">
        									<tbody>
        										<tr>
        											<td style="vertical-align: top;">
        												<table style="width: 100%;" border="0" cellspacing="0"
        													cellpadding="0" align="left">
        													<tbody>
        														<tr>
        															<td style="vertical-align: top;">
        																<table style="width: 100%;" border="0" cellspacing="0"
        																	cellpadding="0">
        																	<tbody>
        																		<tr>
        																			<td style="vertical-align: top;">
        																				<p style="text-align: left;">
        																					<span style="color: #808080; font-size: 12pt;"><strong>&lt;&lt;subject&gt;&gt;</strong></span>
        																				</p>
        																			</td>
        																		</tr>
        																		<tr>
        																			<td style="vertical-align: top;">
        																				<p style="text-align: left;">
        																					<span style="color: #808080; font-size: 12pt;">Hi,</span><br /> <br /> <span style="color: #808080; font-size: 12pt;">
                                                                                                &lt;&lt;message&gt;&gt;
                                                                                        </span></p><br>
																						<div style="text-align:center">
															                                <a title="Click to login" href="' . $file_link . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 15px 32px; text-align: center; text-decoration: none; display: inline-block; margin: 4px 2px; cursor: pointer; font-size: 10px;">Click to login</a>
															                            </div>
        																				<p style="text-align: left;">
        																					<span style="color: #808080; font-size: 12pt;">Thank
        																						you,<br />'.Yii::$app->params["supportEmail"].' -  '.Yii::$app->params["supportPhone"].'
        																					</span>
        																				</p>
        																				<p style="text-align: left;">
        																					<span style="color: #808080; font-size: 12pt;">Sky
        																						Tech Analytics</span>
        																				</p>
        																			</td>
        																		</tr>
        																	</tbody>
        																</table>
        															</td>
        														</tr>
        													</tbody>
        												</table>
        											</td>
        										</tr>
        									</tbody>
        								</table>
        							</td>
        						</tr>
        						<tr style="text-align: center;">
        							<td style="vertical-align: top; width: 1008px;">
        								<table style="width: 100%;" border="0" cellspacing="0"
        									cellpadding="0">
        									<tbody>
        										<tr>
        											<td style="vertical-align: top;">
        												<table style="width: 100%;" border="0" cellspacing="0"
        													cellpadding="0" align="left">
        													<tbody>
        														<tr>
        															<td style="vertical-align: top; text-align: center;"><span
        																style="font-size: 8pt; color: #808080;"><em>Copyright&nbsp;&copy;&nbsp;</em>2017
        																	Sky Tech Analytics.<em>&nbsp;All rights reserved.</em></span><br />
        																<br /> <span style="color: #808080;"> <strong>Our
        																		mailing address is:</strong></span><br /> <span
        																style="color: #808080;"> 18 Interchange Blvd. Suite A
        																	Greenville, SC 29607</span></td>
        														</tr>
        													</tbody>
        												</table>
        											</td>
        										</tr>
        									</tbody>
        								</table>
        							</td>
        						</tr>
        					</tbody>
        				</table>
        			</td>
        		</tr>
        	</tbody>
        </table>';
        
        $subject = 'You have received a new message.';
        
        if($sender_type == 'Client'){
            $message = 'Company "'.$sender_details['company_name'].'" with company number "'.$sender_details['company_client_number'].'" of Client "'.$sender_details['client_name'].'" with client number "'.$sender_details['client_number'].'" has sent you a new message on a file review conversation. Please login to your account to check the details.';
        }else{
            $message = 'You have received a new message on a file review conversation. Please login to your account to check the details.';
        }
        
        if($msg_type == 'NewFile'){
            $subject = 'A new file has been uploaded.';
            $message = 'Company "'.$sender_details['company_name'].'" with company number "'.$sender_details['company_client_number'].'" of Client "'.$sender_details['client_name'].'" with client number "'.$sender_details['client_number'].'" has uploaded a new file. Please login to your account to check the details.';
        }
        
        $body = str_replace("&lt;&lt;subject&gt;&gt;", $subject, $body);
        $body = str_replace("&lt;&lt;message&gt;&gt;", $message, $body);
        
        $status = ResourceComponent::sendMail(Yii::$app->params['supportEmail'], $to_email, $subject, $body);
     
        return $status;
    }
    
    /**
     * [getFirstName description]
     * @method getFirstName
     * @param  [type]       $user [description]
     * @return [type]             [description]
     */
    public static function getFirstName($user)
    {
        $firstName = $user->username;
        switch ($user->user_type) {
          case '2':
          $adminUser = $user->adminUserDetails;
            if ($adminUser && $adminUser->first_name) {
                $firstName = $adminUser->first_name;
            }
          break;
          case '3':
          $clientUser = $user->clientUserDetails;
          if ($clientUser && $clientUser->purchaser_first_name) {
              $firstName = $clientUser->purchaser_first_name;
          }
            break;
          case '4':
            $companyUser = $user->companyUserDetails;
            if ($companyUser && $companyUser->first_name) {
                $firstName = $companyUser->first_name;
            }
            break;
        default:
          $firstName = $user->username;
          break;
      }
        return $firstName;
    }
    /**
     * [replaceEmailAndPhone description]
     * @method replaceEmailAndPhone
     * @param  [type]               $body  [description]
     * @param  [type]               $brand [description]
     * @return [type]                      [description]
     */
    public static function replaceEmailAndPhone($body,$user)
    {
      $email = self::DEFAULT_EMAIL;
      $phoneNumber = self::DEFAULT_NUMBER;
      $brand = '';
      switch($user->user_type)
      {
        case '1':
        case '2':
          $brand['support_email'] = Yii::$app->params['supportEmail'];
          $brand['support_phone'] = Yii::$app->params['supportPhone'];
          $brand['brand_logo'] = Yii::$app->params['supportLogo'];

          break;
        case '3':
          $info = Clients::getDefaultClientUserInformation($user->user_id);
          if($info['brand']){
            $brand = $info['brand'];
          }
        break;
        case '4':
        $info = CompanyUsers::getCompanyUserInformation($user->user_id);
        if($info['brand']){
          $brand = $info['brand'];
        }
        break;
      }

      if($brand && $brand['support_phone']){
        $phoneNumber = $brand['support_phone'];
      }
      if($brand && $brand['support_email']){
        $email = $brand['support_email'];
      }
      $phoneNumber = preg_replace('/\(|\)|\-|\ /', '', $phoneNumber);
      $phoneNumber = substr($phoneNumber,0,3).'-'.substr($phoneNumber,3,3).'-'.substr($phoneNumber,6,4);
      $body = str_replace("&lt;&lt;support-email&gt;&gt;", $email, $body);
      $body = str_replace("&lt;&lt;support-number&gt;&gt;", $phoneNumber, $body);
      $return['body'] = $body;
      $return['brand'] = $brand;

      return $return;
    }
    
    /**
     * Inform the client purchase user about the forms that are approved
     */
    public static function sendFormApprovalMail($data)
    {
        $status = '';
        $formApprovalMail = EmailTemplates::find()->where(['template_id' => 7])->One();

        if (!empty($formApprovalMail)) {
            
            $user = \app\models\User::find()->where(['username'=>$data['to']])->one();   
            $firstName = (!empty($user)) ? self::getFirstName($user) : $data['to'];
            // removing place holders in mail body
            $body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $formApprovalMail->body);
            $body = str_replace("&lt;&lt;company_name&gt;&gt;", $data['companyName'], $body);
            $body = str_replace("&lt;&lt;company_number&gt;&gt;", $data['companyNumber'], $body);
            $body = str_replace("&lt;&lt;client_number&gt;&gt;", $data['clientName'], $body);
            
            $response = self::replaceDynamicVariables($body,$user);
            $body = $response['body'];
            $brand = $response['brand'];
            $status  = ResourceComponent::sendMail($brand['support_email'], $data['to'], $formApprovalMail->subject, $body);
        }
        return $status;
    }
    
    /**
     * Inform the users about the forms print and mail schedule status
     */
    public static function sendFormPrintAndMailStatusMail($data)
    {
        $status = '';
        $formApprovalMail = EmailTemplates::find()->where(['template_id' => 8])->One();

        if (!empty($formApprovalMail)) {
			
			$user = \app\models\User::find()->where(['user_id'=>$data['client_user_id']])->one();   
            $firstName = (!empty($user)) ? self::getFirstName($user) : $data['client_user_id'];
            // removing place holders in mail body
            $body = str_replace("&lt;&lt;company_name&gt;&gt;", $data['companyName'], $formApprovalMail->body);
            $body = str_replace("&lt;&lt;company_number&gt;&gt;", $data['companyNumber'], $body);
            $body = str_replace("&lt;&lt;no_of_forms&gt;&gt;", $data['no_of_forms'], $body);
            $body = str_replace("&lt;&lt;person_type&gt;&gt;", $data['person_type'], $body);
            $body = str_replace("&lt;&lt;total_amount&gt;&gt;", $data['total_amount'], $body);
			$body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $body);
            
            $body = str_replace("&lt;&lt;support-email&gt;&gt;", Yii::$app->params['supportEmail'], $body);
            $body = str_replace("&lt;&lt;support-number&gt;&gt;", Yii::$app->params['supportPhone'], $body);
            
            try {
                $sendGrid = Yii::$app->sendGrid;
                
                $fromMail = \Yii::$app->params['supportEmail'];
                $message = $sendGrid->compose();

                $status = $message->setFrom($fromMail)
                              ->setTo([$data['account_settings_mail'],$data['client_mail'],$data['account_manager_mail']])
                              //->setCc($data['client_mail'])
                             // ->setBcc($data['account_manager_mail'])
                              ->setReplyTo($fromMail)
                              ->setSubject($formApprovalMail->subject)
                              ->setHtmlBody($body)
                              ->send($sendGrid);
							  
							  
                if (!$status) {
			    $error = "Error in mail triggering ".implode(',', array_values($sendGrid->getErrors()));
                    throw new Exception(Json::encode($error), 1);
                }
            } catch (Exception $e) {
				
                throw new Exception(json_encode($e->getMessage()), 1);
            }
        }
        return $status;
    }

    /**
     * Inform the users about the forms print and mail schedule status
     */
    public static function sendRequestFormMail($data)
    {
        $status = '';
        $requestForm = EmailTemplates::find()->where(['template_id' => 9])->one();

        if (!empty($requestForm)) {

            $firstName = (isset($data['user']) && !empty($data['user'])) ? self::getFirstName($data['user']) : $data['toEmail'];
            // removing place holders in mail body
            $body = str_replace("&lt;&lt;company_name&gt;&gt;", $data['companyName'], $requestForm->body);
            $body = str_replace("&lt;&lt;company_number&gt;&gt;", $data['companyNumber'], $body);
            $body = str_replace("&lt;&lt;name&gt;&gt;", $firstName, $body);
            $body = str_replace("&lt;&lt;support-email&gt;&gt;", Yii::$app->params['supportEmail'], $body);
            $body = str_replace("&lt;&lt;support-number&gt;&gt;", Yii::$app->params['supportPhone'], $body);

            try {
                $sendGrid = Yii::$app->sendGrid;

                $fromMail = \Yii::$app->params['supportEmail'];
                $message = $sendGrid->compose();

                $status = $message->setFrom($fromMail)
                    ->setTo([$data['toEmail']])
                    ->setReplyTo($fromMail)
                    ->setSubject($requestForm->subject)
                    ->setHtmlBody($body)
                    ->send($sendGrid);

                if (!$status) {
                    $error = "Error in mail triggering " . implode(',', array_values($sendGrid->getErrors()));
                    throw new Exception(Json::encode($error), 1);
                }
            } catch (Exception $e) {

                throw new Exception(json_encode($e->getMessage()), 1);
            }
        }
        return $status;
    }

}
