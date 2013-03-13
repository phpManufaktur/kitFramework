<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
namespace phpManufaktur\Basic\Control;

use Symfony\Component\Validator\Constraints as Assert;
use phpManufaktur\Basic\Data\Security\Users;

class forgottenPassword
{

    protected $app = null;

    /**
     * Constructor
     */
    public function __construct ()
    {
        global $app;
        $this->app = $app;
    } // __construct()

    /**
     * Display a dialog to enter the email address and order a new password
     *
     * @param string $message
     * @return string dialog
     */
    public function dialogForgottenPassword($message='')
    {
        $form = $this->app['form.factory']->createBuilder('form')
        ->add('email', 'text', array())
        ->getForm();
        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'forgot.password.twig'),
            array('form' => $form->createView(), 'message' => $message));
    } // dialogForgottenPassword()

    /**
     * User entered a email address and get now a link to enter a new password
     *
     * @return string dialog
     */
    public function dialogResetPassword()
    {
        // get the form values
        $form = $this->app['request']->get('form');
        // validate the email
        $errors = $this->app['validator']->validateValue($form['email'], new Assert\Email());
        if (count($errors) > 0) {
            // invalid email
            $message = '';
            foreach ($errors as $error) {
                $message .= sprintf('<p>%s</p>', $error->getMessage());
            }
            return $this->dialogForgottenPassword($message);
        }
        $Users = new Users();
        if (false === ($user = $Users->selectUser($form['email']))) {
            $message = '<p>There exists no user with the submitted email address.</p>';
            $this->app['monolog']->addDebug(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $message));
            return $this->dialogForgottenPassword($message);
        }
        // email address is valid, so we can create a new GUID and send a mail
        if (false === ($guid = $Users->createNewGUID($form['email']))) {
            $message = '<p>Can\'t create a new GUID as long the last GUID is not expired. You must wait 24 hours between the creation of new passwords.</p>';
            $this->app['monolog']->addDebug(sprintf('[%s - %s] %s', __METHOD__, __LINE__, 'No GUID created, last creation within the last 24 hours.'));
            return $this->dialogForgottenPassword($message);
        }

        // create the email body
        $body = $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'mail/create.password.twig'),
            array('name' => $user['displayname'], 'server' => FRAMEWORK_URL,
                'reset_password_url' => FRAMEWORK_URL.'/password/create/'.$guid['guid']
            ));
        // create the message
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('kitFramework password reset'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS))
        ->setTo(array($form['email']))
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $this->app['mailer']->send($message);

        // show a response dialog
        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'create.password.twig'),
            array('email' => $form['email']));
    } // dialogResetPassword()

    /**
     * Dialog to create a new password after verification of the submitted GUID
     *
     * @param string $guid
     * @return string dialog
     */
    public function dialogCreatePassword($guid)
    {
        // first check the GUID
        $Users = new Users();
        if (false === ($user = $Users->selectUserByGUID($guid))) {
            // GUID does not exists
            return $this->app['twig']->render($this->app['utils']->templateFile('@framework', 'message.twig'),
                array('title' => 'Create a new password',
                    'message' => $this->app['translator']->trans(
                        '<p>Sorry, but the submitted GUID is invalid.</p><p>Please contact the webmaster.</p>')
                ));
        }
        if ($user['guid_status'] != 'ACTIVE') {
            // the GUID was already used
            return $this->app['twig']->render($this->app['utils']->templateFile('@framework', 'message.twig'),
                array('title' => 'Create a new password',
                    'message' => $this->app['translator']->trans(
                        '<p>The submitted GUID was already used and is no longer valid.</p><p>Please <a href="%password_forgotten%">order a new link</a>.</p>',
                        array('%password_forgotten%' => $this->app['url_generator']->generate('password_forgotten')))
                ));
        }
        // check if the GUID is used within 24 hours
        $d = strtotime($user['guid_timestamp']);
        $limit = mktime(date('H', $d) + Users::getGuidWaitHoursBetweenResets(),
            date('i', $d), date('s', $d), date('m', $d), date('d', $d), date('Y', $d));
        if (time() > $limit) {
            // the GUID is expired
            return $this->app['twig']->render(
                $this->app['utils']->templateFile('@framework', 'message.twig'),
                array(
                    'title' => 'Create a new password',
                    'message' => $this->app['translator']->trans(
                        '<p>The submitted GUID is expired and no longer valid.</p><p>Please <a href="%password_forgotten%">order a new link</a>.</p>',
                        array('%password_forgotten%' => $this->app['url_generator']->generate('password_forgotten')))
                    )
                );
        }
        // ok - the GUID is valid, so lock the GUID and show the dialog to create a password
        $data = array(
            'guid_status' => 'LOCKED'
        );
        // update record
        $Users->updateUser($user['username'], $data);

        $form = $this->app['form.factory']->createBuilder('form')
        ->add('password', 'repeated', array(
            'type' => 'password',
            'required' => true,
            'first_options' => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password'),
            ))
        ->add('username', 'hidden', array(
            'data' => $user['username']
            ))
        ->getForm();

        return $this->app['twig']->render(
            $this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'set.password.twig'),
            array(
                'form' => $form->createView(),
                'message' => $this->app['translator']->trans('<p>Hello %name%,</p><p>you want to change your password, so please type in a new one, repeat it and submit the form.</p><p>If you won\'t change your password just leave this dialog.</p>', array('%name%' => $user['displayname']))
                )
            );
    } // dialogCreatePassword()

    public function dialogRetypePassword() {
        // get the form values
        $form = $this->app['request']->get('form');

        if ($form['password']['first'] != $form['password']['second']) {
            // the passwords does not match
            $message = $this->app['translator']->trans('<p>The both passwords you have typed in does not match, please try again!</p>');
        }
        elseif ($this->app['utils']->passwordStrength($form['password']['first']) < 3) {
            // the password is not strength enough
            $message = $this->app['translator']->trans('<p>The password you have typed in is not strength enough.</p><p>Please choose a password at minimun 8 characters long, containing lower and uppercase characters, numbers and special chars. Spaces are not allowed.</p>');
        }
        else {
            // change the password and prompt info
            $passwordEncoder = new manufakturPasswordEncoder();
            // we don't use "salt"
            $password = $passwordEncoder->encodePassword($form['password']['first'], '');

            // update the user data
            $Users = new Users();
            $Users->updateUser($form['username'], array('password' => $password));
            // return a info message and leave the dialog
            return $this->app['twig']->render($this->app['utils']->templateFile('@framework', 'message.twig'),
                array('title' => 'Password changed',
                    'message' => $this->app['translator']->trans(
                        '<p>The password for the kitFramework was successfull changed.</p><p>You can now <a href="%login%">login using the new password</a>.</p>',
                        array('%login%' => $this->app['url_generator']->generate('login')))
                ));
        }

        // changing the password was not successfull, show again the dialog
        $form = $this->app['form.factory']->createBuilder('form')
        ->add('password', 'repeated', array(
            'type' => 'password',
            'required' => true,
            'first_options' => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password'),
            ))
        ->add('username', 'hidden', array(
            'data' => $form['username']
            ))
        ->getForm();

        return $this->app['twig']->render(
            $this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'set.password.twig'),
            array(
                'form' => $form->createView(),
                'message' => $message
            )
        );
    } // dialogRetypePassword()

} // class forgotPassword