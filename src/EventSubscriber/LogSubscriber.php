<?php

namespace App\EventSubscriber;

use App\Entity\Log;
use App\Controller\LogManagerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;

class LogSubscriber implements EventSubscriberInterface
{    
    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $em)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }    
    
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof LogManagerInterface) {
            $route = $event->getRequest()->attributes->get('_route');            
            $actions = ['new', 'edit', 'delete'];
            $match = (str_replace($actions, '', $route) != $route);
           
            if ($match && ($event->getRequest()->isMethod('POST') || $event->getRequest()->isMethod('DELETE'))) {
                $this->user = $this->tokenStorage->getToken()->getUser();
                $content = '';                
                $user = 'id: '.$this->user->getId().' name: '.$this->user->getUsername();
                $action = $event->getRequest()->attributes->get('_controller');                
                $params = $event->getRequest()->attributes->get('_route_params');
                                                               
                foreach ($params as $param) {
                    $content .= 'id: '.$param;
                }
                
                if (empty($content)) {
                    $formArray = [];
                    $formContent = $event->getRequest()->getContent();
                    parse_str($formContent, $formArray); 
                    $result = call_user_func_array('array_merge', $formArray);

                    foreach ($result as $key=>$res) {
                        if (is_array($res)) {
                            $content .= $key.': '. implode(' ', $res);
                        } else {
                            $content .= $key.': '.$res.' ';
                        }
                    }
                }
               
                $date = new \DateTime();
                $log = new Log($action, $user, $content, $date);
                $this->em->persist($log);
                $this->em->flush();               
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    } 
}