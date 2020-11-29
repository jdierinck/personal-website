<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Email;

class DefaultController extends Controller
{
    public const HONEYPOT_FIELD_NAME = 'url';

    /**
     * @Route("/{_locale}", defaults={"_locale":"nl"}, requirements={"_locale":"nl|en|fr"}, name="homepage")
     */
    public function indexAction(Request $request)
    {
		$defaultData = array();
	
		$form = $this->createFormBuilder($defaultData)
			// add anchor to make sure the browser scrolls down to the form after it is submitted		
 			->setAction('#contact')
			->add('name', TextType::class, array(
			'label' => 'form.contact.name',
			'constraints' => new NotBlank(),
			))
			->add('email', EmailType::class, array(
			'label' => 'form.contact.email',
			'constraints' => array(new NotBlank(), new Email()),
			))
			->add('subject', TextType::class, array(
			'label' => 'form.contact.subject',
			))
			->add('message', TextareaType::class, array(
			'label' => 'form.contact.message',
			'attr' => array('rows' => 5, 'cols' => 50),
			'constraints' => new NotBlank(),
			))
			->add(self::HONEYPOT_FIELD_NAME, TextType::class, array(
			'label' => false,
			'required' => false,
			// 'constraints' => new Blank(),
			'attr' => array(
				'tabindex' => '-1', // prevent tabbing into this field
				'autocomplete' => 'off', // prevent browser autocomplete
				'class' => 'contactform-url', // hide it!
			),
			))
			->add('send', SubmitType::class, array('label' => 'form.contact.send'))
			->getForm();
			    
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			// data is an array with "name", "email", "subject" and "message" keys
			$data = $form->getData();
			
			if (empty($data[self::HONEYPOT_FIELD_NAME])) {
				// Send e-mail to myself
				$message = \Swift_Message::newInstance()
					->setSubject($data['subject'])
					//->setFrom($data['email'])
					->setFrom($this->getParameter('mailer_from'))
					->setTo($this->getParameter('mailer_to'))
					->setBody(
						$this->renderView(
							// appBundle/views/emails/contact.html.twig
							'AppBundle:emails:contact.html.twig',
							array('data' => $data)
							),
							'text/html'
						)
					/*
					 * If you also want to include a plaintext version of the message
					->addPart(
						$this->renderView(
							'Emails/registration.txt.twig',
							array('name' => $name)
						),
						'text/plain'
					)
					*/
				;
				$this->get('mailer')->send($message);

				//Log message to file
				$file = $this->container->getParameter('kernel.root_dir').'/logs/mails.txt';

				// add flash message
				$translator = $this->get('translator');
				$this->addFlash(
					'notice',
					$translator->trans('<strong>Bedankt!</strong> Ik neem zo snel mogelijk contact met je op.')
				);
				} else {
				// Spam!
                $message = sprintf("[%s]\nSPAM detected: email: '%s', honeypot content: '%s', begin message: '%s...'\n\n", (new \DateTime)->format('c'), $data['email'], $data[self::HONEYPOT_FIELD_NAME], substr($data['message'], 0, 50));

				$file = $this->container->getParameter('kernel.root_dir').'/logs/spam.txt';
			}
			
			file_put_contents($file, $message, FILE_APPEND);

        			
			return $this->redirectToRoute('homepage'); 
		}

		// TO DO: log form errors

		return $this->render('AppBundle:default:index.html.twig', array('form' => $form->createView()));    

    }
    
}
