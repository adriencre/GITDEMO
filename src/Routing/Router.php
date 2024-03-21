<?php

declare(strict_types=1);

namespace MyApp\Routing;

use MyApp\Controller\DefaultController;
use MyApp\Controller\OrderController;
use MyApp\Controller\UserController;
use MyApp\Service\DependencyContainer;

class Router
{
    private $dependencyContainer;
    private $pageMappings;
    private $defaultPage;
    private $errorPage;

    public function __construct(DependencyContainer $dependencyContainer)
    {
        $this->dependencyContainer = $dependencyContainer;

        $this->pageMappings = [
            'home' => [DefaultController::class, 'home', []],
            'contact' => [DefaultController::class, 'contact', []],
            'produit' => [DefaultController::class, 'produit', []],
            'types' => [DefaultController::class, 'types', []],
            'updateType' => [DefaultController::class, 'updateType', []],
            'addType' => [DefaultController::class, 'addType', []],
            'deleteType' => [DefaultController::class, 'deleteType', []],
            'user' => [DefaultController::class, 'user', ["admin"]],
            'updateUser' => [DefaultController::class, 'updateUser', ["admin"]],
            'addUser' => [DefaultController::class, 'addUser', []],
            'deleteUser' => [DefaultController::class, 'deleteUser', []],
            '404' => [DefaultController::class, 'error404', []],
            '500' => [DefaultController::class, 'error500', []],
            'orders' => [OrderController::class, 'orders', []],
            'login' => [DefaultController::class, 'login', []],
            'logout' => [DefaultController::class, 'logout', []],
            '403' => [DefaultController::class, 'error403', []],
            'profile' => [DefaultController::class, 'profile', []],
            'updatePassword' => [DefaultController::class, 'updatePassword', []],
            'deleteProduit' => [DefaultController::class, 'deleteProduit',[]],
            'updateProduit' => [DefaultController::class, 'updateProduit',[]],
            'cart' => [DefaultController::class, 'cart',[]],
            'addcart' => [DefaultController::class, 'addcart',[]],
            'additem' => [DefaultController::class, 'additem',[]],
            'productType' => [DefaultController::class, 'productType',[]],
            'updateCart' => [DefaultController::class, 'updateCart',[]],
            'deleteCartConfirm' => [DefaultController::class, 'deleteCartConfirm',[]],
            'viewReviews' => [DefaultController::class, 'viewReviews',[]],
            'addReviewForm' => [DefaultController::class, 'addReviewForm',[]],
            'submitReview' => [DefaultController::class, 'submitReview',[]],
            'addProduct' => [DefaultController::class, 'addProduct',[]],
            'myCart' => [DefaultController::class, 'myCart',[]],
            'cartProducts' => [DefaultController::class, 'cartProducts',[]],
            'addToCart' => [DefaultController::class, 'addToCart',[]],
        ];
        $this->defaultPage = 'home';
        $this->errorPage = '404';
    }

    public function route($twig)
    {
        $requestedPage = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);

        if (!$requestedPage) {
            $requestedPage = $this->defaultPage;
        } else {
            if (!array_key_exists($requestedPage, $this->pageMappings)) {
                $requestedPage = $this->errorPage;
            }
        }

        $controllerInfo = $this->pageMappings[$requestedPage];
        [$controllerClass, $method, $requiredRoles] = $controllerInfo;

        if ($this->checkUserPermissions($requiredRoles)) {
            if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
                $controller = new $controllerClass($twig, $this->dependencyContainer);
                call_user_func([$controller, $method]);
            } else {
                $this->handleError($twig, '500');
            }
        } else {
            $this->handleError($twig, '403');
        }
    }

    private function checkUserPermissions(array $requiredRoles): bool
    {
        if (!empty($requiredRoles)) {
            if (isset($_SESSION['roles'])) {
                $i = array_intersect($_SESSION['roles'], $requiredRoles);
                if (empty($i)) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    private function handleError($twig, $errorCode)
    {
        $errorInfo = $this->pageMappings[$errorCode];
        [$errorControllerClass, $errorMethod] = $errorInfo; 
        $errorController = new $errorControllerClass($twig, $this->dependencyContainer);
        call_user_func([$errorController, $errorMethod]);
    }
    
}
