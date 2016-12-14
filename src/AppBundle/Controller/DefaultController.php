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
			->add('honeypot', HiddenType::class, array(
			'label' => 'form.contact.honeypot',
			'constraints' => new Blank(),
			))
			->add('send', SubmitType::class, array('label' => 'form.contact.send'))
			->getForm();
			    
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			// data is an array with "name", "email", "subject" and "message" keys
			$data = $form->getData();
			
			// Send e-mail to myself
			$message = \Swift_Message::newInstance()
				->setSubject($data['subject'])
				->setFrom($data['email'])
				->setTo('johan.dierinck@telenet.be')
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
			
			// add flash message
			$translator = $this->get('translator');
			
        	$this->addFlash(
            	'notice',
            	$translator->trans('<strong>Bedankt!</strong> Ik neem zo snel mogelijk contact met je op.')
        	);
        			
			return $this->redirectToRoute('homepage'); 
		}

		return $this->render('AppBundle:default:index.html.twig', array('form' => $form->createView()));    
        // replace this example code with whatever you need
//         return $this->render('default/index.html.twig', array(
//             'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
//         ));
    }
    
}
