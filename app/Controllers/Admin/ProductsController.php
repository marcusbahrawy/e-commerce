<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\AuditLogRepository;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Http\Middleware\PageCacheMiddleware;
use App\Support\Auth;
use App\Support\Slug;

class ProductsController
{
    public function __construct(
        private ProductRepository $productRepo,
        private CategoryRepository $categoryRepo,
        private BrandRepository $brandRepo,
        private AuditLogRepository $auditLogRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $products = $this->productRepo->listAllForAdmin(100, 0);
        $html = $this->render('admin/products/index', ['title' => 'Produkter', 'products' => $products]);
        return Response::html($html);
    }

    public function createForm(Request $request, array $params): Response
    {
        $categories = $this->categoryRepo->listAllForAdmin();
        $brands = $this->brandRepo->listAll();
        $html = $this->render('admin/products/form', ['title' => 'Nytt produkt', 'product' => null, 'categories' => $categories, 'brands' => $brands]);
        return Response::html($html);
    }

    public function create(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/produkter', 302);
        }
        $title = trim($request->input('title', '') ?? '');
        $slug = trim($request->input('slug', '') ?? '') ?: Slug::from($title);
        if ($title === '') {
            $categories = $this->categoryRepo->listAllForAdmin();
            $brands = $this->brandRepo->listAll();
            $html = $this->render('admin/products/form', ['title' => 'Nytt produkt', 'product' => null, 'categories' => $categories, 'brands' => $brands, 'error' => 'Tittel er påkrevd.']);
            return Response::html($html);
        }
        if ($this->productRepo->slugExists($slug)) {
            $slug = $slug . '-' . time();
        }
        $priceOre = (int) ($request->input('price_from_ore', '0') ?? 0);
        $primaryCategoryId = $request->input('primary_category_id', '');
        $brandId = $request->input('brand_id', '');
        $brandId = $brandId !== '' && (int) $brandId > 0 ? (int) $brandId : null;
        $id = $this->productRepo->create([
            'slug' => $slug,
            'title' => $title,
            'subtitle' => trim($request->input('subtitle', '') ?? '') ?: null,
            'sku' => trim($request->input('sku', '') ?? '') ?: null,
            'description_short' => trim($request->input('description_short', '') ?? '') ?: null,
            'description_html' => trim($request->input('description_html', '') ?? '') ?: null,
            'price_from_ore' => $priceOre,
            'price_to_ore' => $priceOre > 0 ? $priceOre : null,
            'brand_id' => $brandId,
            'is_active' => $request->input('is_active', '1') ? 1 : 0,
            'is_featured' => $request->input('is_featured', '0') ? 1 : 0,
        ]);
        if ($primaryCategoryId !== '' && (int) $primaryCategoryId > 0) {
            $this->productRepo->setPrimaryCategory($id, (int) $primaryCategoryId);
            $this->productRepo->setProductCategories($id, [(int) $primaryCategoryId], (int) $primaryCategoryId);
        }
        $this->auditLogRepo->log(Auth::userId(), 'product.create', 'product', (string) $id, $title, $request->ip());
        PageCacheMiddleware::purge(dirname(__DIR__, 3) . '/storage');
        return Response::redirect('/admin/produkter', 302);
    }

    public function editForm(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $product = $this->productRepo->findByIdForAdmin($id);
        if ($product === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $categories = $this->categoryRepo->listAllForAdmin();
        $brands = $this->brandRepo->listAll();
        $images = $this->productRepo->getImages($id);
        $html = $this->render('admin/products/form', ['title' => 'Rediger produkt', 'product' => $product, 'categories' => $categories, 'brands' => $brands, 'images' => $images]);
        return Response::html($html);
    }

    public function uploadImage(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/admin/produkter'), 302);
        }
        $productId = (int) ($params['id'] ?? 0);
        $product = $this->productRepo->findByIdForAdmin($productId);
        if ($product === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $file = $_FILES['image'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Response::redirect(url('/admin/produkter/' . $productId . '/rediger') . '?error=upload', 302);
        }
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed, true)) {
            return Response::redirect(url('/admin/produkter/' . $productId . '/rediger') . '?error=type', 302);
        }
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return Response::redirect(url('/admin/produkter/' . $productId . '/rediger') . '?error=size', 302);
        }
        $root = dirname(__DIR__, 3);
        $dir = $root . '/public/uploads/products/' . $productId;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $ext = preg_replace('/[^a-z0-9]/i', '', $ext) ?: 'jpg';
        $filename = uniqid('', true) . '.' . strtolower($ext);
        $pathAbs = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $pathAbs)) {
            return Response::redirect(url('/admin/produkter/' . $productId . '/rediger') . '?error=save', 302);
        }
        $pathRel = 'uploads/products/' . $productId . '/' . $filename;
        $this->productRepo->addImage($productId, $pathRel, null, null, 0);
        return Response::redirect(url('/admin/produkter/' . $productId . '/rediger'), 302);
    }

    public function deleteImage(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/admin/produkter'), 302);
        }
        $productId = (int) ($params['id'] ?? 0);
        $imageId = (int) ($request->input('image_id', '0') ?? 0);
        $product = $this->productRepo->findByIdForAdmin($productId);
        if ($product === null || $imageId < 1) {
            return Response::redirect(url('/admin/produkter'), 302);
        }
        $image = $this->productRepo->getImageById($imageId);
        if ($image === null || (int) $image['product_id'] !== $productId) {
            return Response::redirect(url('/admin/produkter/' . $productId . '/rediger'), 302);
        }
        $root = dirname(__DIR__, 3);
        foreach (['path_original', 'path_webp'] as $key) {
            if (!empty($image[$key])) {
                $path = $root . '/public/' . ltrim($image[$key], '/');
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
        $this->productRepo->deleteImage($imageId, $productId);
        return Response::redirect(url('/admin/produkter/' . $productId . '/rediger'), 302);
    }

    public function setPrimaryImage(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/admin/produkter'), 302);
        }
        $productId = (int) ($params['id'] ?? 0);
        $imageId = (int) ($request->input('image_id', '0') ?? 0);
        $product = $this->productRepo->findByIdForAdmin($productId);
        if ($product === null) {
            return Response::redirect(url('/admin/produkter'), 302);
        }
        $this->productRepo->setPrimaryImage($productId, $imageId > 0 ? $imageId : null);
        return Response::redirect(url('/admin/produkter/' . $productId . '/rediger'), 302);
    }

    public function update(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/produkter', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $product = $this->productRepo->findByIdForAdmin($id);
        if ($product === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $title = trim($request->input('title', '') ?? '');
        $slug = trim($request->input('slug', '') ?? '') ?: Slug::from($title);
        if ($title === '') {
            $categories = $this->categoryRepo->listAllForAdmin();
            $brands = $this->brandRepo->listAll();
            $images = $this->productRepo->getImages($id);
            $html = $this->render('admin/products/form', ['title' => 'Rediger produkt', 'product' => array_merge($product, ['title' => $request->input('title'), 'slug' => $slug]), 'categories' => $categories, 'brands' => $brands, 'images' => $images, 'error' => 'Tittel er påkrevd.']);
            return Response::html($html);
        }
        if ($this->productRepo->slugExists($slug, $id)) {
            $slug = $product['slug'];
        }
        $priceOre = (int) ($request->input('price_from_ore', '0') ?? 0);
        $brandId = $request->input('brand_id', '');
        $brandId = $brandId !== '' && (int) $brandId > 0 ? (int) $brandId : null;
        $this->productRepo->update($id, [
            'slug' => $slug,
            'title' => $title,
            'subtitle' => trim($request->input('subtitle', '') ?? '') ?: null,
            'sku' => trim($request->input('sku', '') ?? '') ?: null,
            'description_short' => trim($request->input('description_short', '') ?? '') ?: null,
            'description_html' => trim($request->input('description_html', '') ?? '') ?: null,
            'price_from_ore' => $priceOre,
            'price_to_ore' => $priceOre > 0 ? $priceOre : null,
            'brand_id' => $brandId,
            'is_active' => $request->input('is_active', '1') ? 1 : 0,
            'is_featured' => $request->input('is_featured', '0') ? 1 : 0,
        ]);
        $primaryCategoryId = $request->input('primary_category_id', '');
        if ($primaryCategoryId !== '' && (int) $primaryCategoryId > 0) {
            $this->productRepo->setPrimaryCategory($id, (int) $primaryCategoryId);
            $this->productRepo->setProductCategories($id, [(int) $primaryCategoryId], (int) $primaryCategoryId);
        } else {
            $this->productRepo->setProductCategories($id, []);
            $this->productRepo->setPrimaryCategory($id, null);
        }
        $this->auditLogRepo->log(Auth::userId(), 'product.update', 'product', (string) $id, $title, $request->ip());
        PageCacheMiddleware::purge(dirname(__DIR__, 3) . '/storage');
        return Response::redirect('/admin/produkter', 302);
    }

    public function delete(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/produkter', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $product = $this->productRepo->findByIdForAdmin($id);
        if ($product !== null) {
            $this->auditLogRepo->log(Auth::userId(), 'product.delete', 'product', (string) $id, $product['title'] ?? null, $request->ip());
            $this->productRepo->softDelete($id);
            PageCacheMiddleware::purge(dirname(__DIR__, 3) . '/storage');
        }
        return Response::redirect('/admin/produkter', 302);
    }

    private function render(string $view, array $data = []): string
    {
        $base = dirname(__DIR__, 2) . '/Templates';
        $layoutPath = $base . '/admin/layout.php';
        $viewPath = $base . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            return '<p>Side ikke funnet.</p>';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();
        $data['content'] = $content;
        $data['title'] = $data['title'] ?? 'Admin';
        extract($data, EXTR_SKIP);
        ob_start();
        require $layoutPath;
        return (string) ob_get_clean();
    }
}
