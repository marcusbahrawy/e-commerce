<?php

declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\LoginController;
use App\Controllers\Admin\CategoriesController;
use App\Controllers\Admin\OrdersController;
use App\Controllers\Admin\ProductsController;
use App\Controllers\CartController;
use App\Controllers\CatalogController;
use App\Controllers\CheckoutController;
use App\Controllers\CmsController;
use App\Controllers\StripeWebhookController;
use App\Controllers\HomeController;
use App\Http\Middleware\AdminAuthMiddleware;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SessionMiddleware;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PageRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\CartService;
use App\Services\CatalogService;
use App\Services\OrderService;
use App\Services\PaymentService;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

\App\Support\Env::load($root . '/.env');

$request = Request::fromGlobals();
$GLOBALS['__request'] = $request;

$router = new Router();
$categoryRepo = new CategoryRepository();
$productRepo = new ProductRepository();
$cartRepo = new CartRepository();
$cartService = new CartService($cartRepo, $productRepo);
$router->middleware(
    new SecurityHeadersMiddleware(),
    new SessionMiddleware(),
    new CsrfMiddleware(),
    new AdminAuthMiddleware(),
    new \App\Http\Middleware\CartCountMiddleware($cartService)
);

$catalogService = new CatalogService($categoryRepo, $productRepo);
$router->get('/', function (Request $req, array $params) use ($catalogService) {
    $controller = new HomeController($catalogService);
    return $controller($req, $params);
}, 'home');
$catalogController = new CatalogController($catalogService);

$router->get('/kategori/{slug}', fn (Request $req, array $p) => $catalogController->category($req, $p), 'catalog.category');
$router->get('/kategori/{parent}/{slug}', fn (Request $req, array $p) => $catalogController->category($req, $p), 'catalog.category.child');
$router->get('/produkt/{slug}', fn (Request $req, array $p) => $catalogController->product($req, $p), 'catalog.product');
$router->get('/sok', fn (Request $req, array $p) => $catalogController->search($req, $p), 'catalog.search');

$pageRepo = new PageRepository();
$cmsController = new CmsController($pageRepo);
$router->get('/side/{slug}', fn (Request $req, array $p) => $cmsController->page($req, $p), 'cms.page');

$cartController = new CartController($cartService);
$router->get('/handlekurv', fn (Request $req, array $p) => $cartController->index($req, $p), 'cart.index');
$router->post('/handlekurv', fn (Request $req, array $p) => $cartController->add($req, $p), 'cart.add');
$router->post('/handlekurv/fjern', fn (Request $req, array $p) => $cartController->remove($req, $p), 'cart.remove');

$orderRepo = new OrderRepository();
$orderService = new OrderService($orderRepo, $cartRepo);
$stripeConfig = require $root . '/app/Config/stripe.php';
$paymentService = new PaymentService($orderRepo, $stripeConfig['secret_key'] ?? '', $stripeConfig['webhook_secret'] ?? '');
$shippingRepo = new \App\Repositories\ShippingMethodRepository();
$checkoutController = new CheckoutController($cartService, $orderService, $paymentService, $shippingRepo);
$router->get('/kasse', fn (Request $req, array $p) => $checkoutController->index($req, $p), 'checkout.index');
$router->post('/kasse', fn (Request $req, array $p) => $checkoutController->submit($req, $p), 'checkout.submit');
$router->get('/kasse/takk', fn (Request $req, array $p) => $checkoutController->thankYou($req, $p), 'checkout.thank-you');

$stripeWebhookController = new StripeWebhookController($paymentService, $orderRepo);
$router->post('/webhooks/stripe', fn (Request $req, array $p) => $stripeWebhookController->handle($req, $p), 'webhooks.stripe');

$userRepo = new UserRepository();
$adminLoginController = new LoginController($userRepo);
$router->get('/admin/login', fn (Request $req, array $p) => $adminLoginController->show($req, $p), 'admin.login');
$router->post('/admin/login', fn (Request $req, array $p) => $adminLoginController->login($req, $p), 'admin.login.submit');
$router->post('/admin/logout', fn (Request $req, array $p) => $adminLoginController->logout($req, $p), 'admin.logout');
$adminDashboardController = new DashboardController($orderRepo);
$router->get('/admin', fn (Request $req, array $p) => $adminDashboardController->index($req, $p), 'admin.dashboard');
$adminProductsController = new ProductsController($productRepo, $categoryRepo);
$router->get('/admin/produkter', fn (Request $req, array $p) => $adminProductsController->index($req, $p), 'admin.products');
$router->get('/admin/produkter/ny', fn (Request $req, array $p) => $adminProductsController->createForm($req, $p), 'admin.products.create');
$router->post('/admin/produkter', fn (Request $req, array $p) => $adminProductsController->create($req, $p), 'admin.products.store');
$router->get('/admin/produkter/{id}/rediger', fn (Request $req, array $p) => $adminProductsController->editForm($req, $p), 'admin.products.edit');
$router->post('/admin/produkter/{id}', fn (Request $req, array $p) => $adminProductsController->update($req, $p), 'admin.products.update');
$router->post('/admin/produkter/{id}/slett', fn (Request $req, array $p) => $adminProductsController->delete($req, $p), 'admin.products.delete');
$router->post('/admin/produkter/{id}/bilde', fn (Request $req, array $p) => $adminProductsController->uploadImage($req, $p), 'admin.products.uploadImage');
$router->post('/admin/produkter/{id}/bilde/slett', fn (Request $req, array $p) => $adminProductsController->deleteImage($req, $p), 'admin.products.deleteImage');
$router->post('/admin/produkter/{id}/bilde/primar', fn (Request $req, array $p) => $adminProductsController->setPrimaryImage($req, $p), 'admin.products.setPrimaryImage');
$adminCategoriesController = new CategoriesController($categoryRepo);
$router->get('/admin/kategorier', fn (Request $req, array $p) => $adminCategoriesController->index($req, $p), 'admin.categories');
$router->get('/admin/kategorier/ny', fn (Request $req, array $p) => $adminCategoriesController->createForm($req, $p), 'admin.categories.create');
$router->post('/admin/kategorier', fn (Request $req, array $p) => $adminCategoriesController->create($req, $p), 'admin.categories.store');
$router->get('/admin/kategorier/{id}/rediger', fn (Request $req, array $p) => $adminCategoriesController->editForm($req, $p), 'admin.categories.edit');
$router->post('/admin/kategorier/{id}', fn (Request $req, array $p) => $adminCategoriesController->update($req, $p), 'admin.categories.update');
$router->post('/admin/kategorier/{id}/slett', fn (Request $req, array $p) => $adminCategoriesController->delete($req, $p), 'admin.categories.delete');
$adminOrdersController = new OrdersController($orderRepo);
$router->get('/admin/ordrer', fn (Request $req, array $p) => $adminOrdersController->index($req, $p), 'admin.orders');
$router->get('/admin/ordrer/{id}', fn (Request $req, array $p) => $adminOrdersController->show($req, $p), 'admin.orders.show');
$router->post('/admin/ordrer/{id}/status', fn (Request $req, array $p) => $adminOrdersController->updateStatus($req, $p), 'admin.orders.updateStatus');

$adminShippingController = new \App\Controllers\Admin\ShippingMethodsController($shippingRepo);
$router->get('/admin/frakt', fn (Request $req, array $p) => $adminShippingController->index($req, $p), 'admin.shipping');
$router->get('/admin/frakt/ny', fn (Request $req, array $p) => $adminShippingController->createForm($req, $p), 'admin.shipping.create');
$router->post('/admin/frakt', fn (Request $req, array $p) => $adminShippingController->create($req, $p), 'admin.shipping.store');
$router->get('/admin/frakt/{id}/rediger', fn (Request $req, array $p) => $adminShippingController->editForm($req, $p), 'admin.shipping.edit');
$router->post('/admin/frakt/{id}', fn (Request $req, array $p) => $adminShippingController->update($req, $p), 'admin.shipping.update');
$router->post('/admin/frakt/{id}/slett', fn (Request $req, array $p) => $adminShippingController->delete($req, $p), 'admin.shipping.delete');

$adminPagesController = new \App\Controllers\Admin\PagesController($pageRepo);
$router->get('/admin/sider', fn (Request $req, array $p) => $adminPagesController->index($req, $p), 'admin.pages');
$router->get('/admin/sider/ny', fn (Request $req, array $p) => $adminPagesController->createForm($req, $p), 'admin.pages.create');
$router->post('/admin/sider', fn (Request $req, array $p) => $adminPagesController->create($req, $p), 'admin.pages.store');
$router->get('/admin/sider/{id}/rediger', fn (Request $req, array $p) => $adminPagesController->editForm($req, $p), 'admin.pages.edit');
$router->post('/admin/sider/{id}', fn (Request $req, array $p) => $adminPagesController->update($req, $p), 'admin.pages.update');
$router->post('/admin/sider/{id}/slett', fn (Request $req, array $p) => $adminPagesController->delete($req, $p), 'admin.pages.delete');

$menuRepo = new \App\Repositories\MenuRepository();
$adminMenusController = new \App\Controllers\Admin\MenusController($menuRepo, $pageRepo);
$router->get('/admin/menyer', fn (Request $req, array $p) => $adminMenusController->index($req, $p), 'admin.menus');
$router->get('/admin/menyer/{key}', fn (Request $req, array $p) => $adminMenusController->editForm($req, $p), 'admin.menus.edit');
$router->post('/admin/menyer/{key}', fn (Request $req, array $p) => $adminMenusController->update($req, $p), 'admin.menus.update');

$sitemapController = new \App\Controllers\SitemapController($categoryRepo, $productRepo, $pageRepo);
$router->get('/sitemap.xml', fn (Request $req, array $p) => $sitemapController->index($req, $p), 'sitemap');

$accountController = new AccountController($userRepo, $orderRepo);
$router->get('/konto', fn (Request $req, array $p) => $accountController->index($req, $p), 'account.index');
$router->get('/konto/login', fn (Request $req, array $p) => $accountController->loginForm($req, $p), 'account.login');
$router->post('/konto/login', fn (Request $req, array $p) => $accountController->login($req, $p), 'account.login.submit');
$router->get('/konto/registrer', fn (Request $req, array $p) => $accountController->registerForm($req, $p), 'account.register');
$router->post('/konto/registrer', fn (Request $req, array $p) => $accountController->register($req, $p), 'account.register.submit');
$router->post('/konto/logout', fn (Request $req, array $p) => $accountController->logout($req, $p), 'account.logout');
$router->get('/konto/ordre', fn (Request $req, array $p) => $accountController->orders($req, $p), 'account.orders');
$router->get('/konto/ordre/{id}', fn (Request $req, array $p) => $accountController->orderDetail($req, $p), 'account.order.detail');
$router->get('/konto/profil', fn (Request $req, array $p) => $accountController->profileForm($req, $p), 'account.profile');
$router->post('/konto/profil', fn (Request $req, array $p) => $accountController->profileUpdate($req, $p), 'account.profile.update');
$router->get('/konto/passord', fn (Request $req, array $p) => $accountController->passwordForm($req, $p), 'account.password');
$router->post('/konto/passord', fn (Request $req, array $p) => $accountController->passwordUpdate($req, $p), 'account.password.update');
$router->get('/konto/glemt-passord', fn (Request $req, array $p) => $accountController->forgotPasswordForm($req, $p), 'account.forgotPassword');
$router->post('/konto/glemt-passord', fn (Request $req, array $p) => $accountController->forgotPassword($req, $p), 'account.forgotPassword.submit');
$router->get('/konto/tilbakestill-passord', fn (Request $req, array $p) => $accountController->resetPasswordForm($req, $p), 'account.resetPassword');
$router->post('/konto/tilbakestill-passord', fn (Request $req, array $p) => $accountController->resetPassword($req, $p), 'account.resetPassword.submit');

$response = $router->dispatch($request);
if ($response->statusCode() === 404) {
    $path = $root . '/app/Templates/errors/404.php';
    if (is_file($path)) {
        ob_start();
        require $path;
        $response = Response::html((string) ob_get_clean(), 404);
    }
}
$response->send();
