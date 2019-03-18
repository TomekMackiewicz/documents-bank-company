<?php
namespace App\Security;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface {

  protected $router;
  protected $authorizationChecker;

  public function __construct(Router $router, AuthorizationChecker $authorizationChecker) {
    $this->router = $router;
    $this->authorizationChecker = $authorizationChecker;       
  }

  /**
  * @param Array $map
  * @return RedirectResponse
  */
  private function redirectRoleToRoute($map) {
  	foreach($map as $role => $route) {
  		if ($this->authorizationChecker->isGranted($role)) {
  			return new RedirectResponse($this->router->generate($route));
  		}
  		return new RedirectResponse($this->router->generate('fos_user_profile_show')); 
  	}
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

    $map = [
    	'ROLE_ADMIN' => 'index'
    	//'ROLE_DEFAULT'	 => 'customer_index'
    ]; 
    return $this->redirectRoleToRoute($map);
  }
}
