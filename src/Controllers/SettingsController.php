<?php
/**
 * GAC - Controlador de Configuración (Asuntos de email)
 */

namespace Gac\Controllers;

use Gac\Core\Request;
use Gac\Repositories\PlatformRepository;
use Gac\Repositories\SettingsRepository;
use Gac\Services\Email\EmailFilterService;

class SettingsController
{
    private SettingsRepository $settingsRepository;
    private PlatformRepository $platformRepository;

    private const PLATFORMS = [
        'netflix', 'disney', 'prime', 'spotify',
        'crunchyroll', 'paramount', 'chatgpt', 'canva',
    ];

    public function __construct()
    {
        $this->settingsRepository = new SettingsRepository();
        $this->platformRepository = new PlatformRepository();
    }

    public function index(Request $request): void
    {
        $platform = strtolower(trim($request->get('platform', 'netflix')));
        if (!in_array($platform, self::PLATFORMS, true)) {
            $platform = 'netflix';
        }

        $platforms = $this->platformRepository->findAll();
        if (empty($platforms)) {
            $platforms = array_map(static fn (string $name) => [
                'name' => $name,
                'display_name' => ucfirst($name),
                'enabled' => 1,
            ], self::PLATFORMS);
        }

        $subjects = $this->settingsRepository->getEmailSubjectsDetailed($platform);
        $filterEnabled = $this->settingsRepository->isPlatformEnabled($platform);

        $this->renderView('admin/settings/index', [
            'title' => 'Asuntos de Email',
            'platforms' => $platforms,
            'current_platform' => $platform,
            'subjects' => $subjects,
            'filter_enabled' => $filterEnabled,
        ]);
    }

    public function storeSubject(Request $request): void
    {
        if ($request->method() !== 'POST') {
            json_response(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        $platform = strtolower(trim($request->input('platform', '')));
        $value = SettingsRepository::normalizeSubjectValue($request->input('value', ''));

        if (!in_array($platform, self::PLATFORMS, true)) {
            json_response(['success' => false, 'message' => 'Plataforma no válida'], 400);
            return;
        }

        if ($value === '') {
            json_response(['success' => false, 'message' => 'El asunto no puede estar vacío'], 400);
            return;
        }

        if (strlen($value) > 500) {
            json_response(['success' => false, 'message' => 'El asunto es demasiado largo (máx. 500 caracteres)'], 400);
            return;
        }

        $duplicate = $this->settingsRepository->findSubjectByExactValue($platform, $value);
        if ($duplicate !== null) {
            json_response([
                'success' => false,
                'message' => sprintf(
                    'Ya existe el asunto %s con el mismo texto. Edítalo o elimínalo primero.',
                    $duplicate['name']
                ),
                'existing' => $duplicate,
            ], 409);
            return;
        }

        $key = $this->settingsRepository->getNextSubjectKey($platform);
        $displayName = $this->getPlatformDisplayName($platform);
        $description = "Asunto para emails de {$displayName}";

        if (!$this->settingsRepository->save($key, $value, 'string', $description)) {
            json_response(['success' => false, 'message' => 'No se pudo guardar el asunto'], 500);
            return;
        }

        $this->reloadEmailPatterns();

        json_response([
            'success' => true,
            'message' => 'Asunto agregado correctamente',
            'subject' => ['name' => $key, 'value' => $value],
        ], 201);
    }

    public function updateSubject(Request $request): void
    {
        if ($request->method() !== 'POST') {
            json_response(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        $platform = strtolower(trim($request->input('platform', '')));
        $name = strtoupper(trim($request->input('name', '')));
        $value = SettingsRepository::normalizeSubjectValue($request->input('value', ''));

        if (!in_array($platform, self::PLATFORMS, true)) {
            json_response(['success' => false, 'message' => 'Plataforma no válida'], 400);
            return;
        }

        if (!$this->settingsRepository->isSubjectKeyForPlatform($name, $platform)) {
            json_response(['success' => false, 'message' => 'Asunto no válido para esta plataforma'], 400);
            return;
        }

        if ($value === '') {
            json_response(['success' => false, 'message' => 'El asunto no puede estar vacío'], 400);
            return;
        }

        $duplicate = $this->settingsRepository->findSubjectByExactValue($platform, $value, $name);
        if ($duplicate !== null) {
            json_response([
                'success' => false,
                'message' => sprintf(
                    'Ese texto ya está en %s. Los textos parecidos (ej. con una letra de más) se permiten si no son idénticos.',
                    $duplicate['name']
                ),
                'existing' => $duplicate,
            ], 409);
            return;
        }

        $displayName = $this->getPlatformDisplayName($platform);
        if (!$this->settingsRepository->save($name, $value, 'string', "Asunto para emails de {$displayName}")) {
            json_response(['success' => false, 'message' => 'No se pudo actualizar el asunto'], 500);
            return;
        }

        $this->reloadEmailPatterns();

        json_response([
            'success' => true,
            'message' => 'Asunto actualizado correctamente',
        ]);
    }

    public function destroySubject(Request $request): void
    {
        if ($request->method() !== 'POST') {
            json_response(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        $platform = strtolower(trim($request->input('platform', '')));
        $name = strtoupper(trim($request->input('name', ''));

        if (!in_array($platform, self::PLATFORMS, true)) {
            json_response(['success' => false, 'message' => 'Plataforma no válida'], 400);
            return;
        }

        if (!$this->settingsRepository->isSubjectKeyForPlatform($name, $platform)) {
            json_response(['success' => false, 'message' => 'Asunto no válido para esta plataforma'], 400);
            return;
        }

        if (!$this->settingsRepository->get($name)) {
            json_response(['success' => false, 'message' => 'El asunto ya no existe en la base de datos'], 404);
            return;
        }

        if (!$this->settingsRepository->delete($name)) {
            json_response(['success' => false, 'message' => 'No se pudo eliminar el asunto'], 500);
            return;
        }

        SettingsRepository::clearCache();
        $this->reloadEmailPatterns();

        json_response([
            'success' => true,
            'message' => 'Asunto eliminado correctamente',
        ]);
    }

    public function togglePlatform(Request $request): void
    {
        if ($request->method() !== 'POST') {
            json_response(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        $platform = strtolower(trim($request->input('platform', '')));
        $enabled = filter_var($request->input('enabled', false), FILTER_VALIDATE_BOOLEAN);

        if (!in_array($platform, self::PLATFORMS, true)) {
            json_response(['success' => false, 'message' => 'Plataforma no válida'], 400);
            return;
        }

        $settingName = 'HABILITAR_' . strtoupper($platform);
        $displayName = $this->getPlatformDisplayName($platform);

        if (!$this->settingsRepository->save(
            $settingName,
            $enabled ? '1' : '0',
            'boolean',
            "Habilitar lectura de emails para {$displayName}"
        )) {
            json_response(['success' => false, 'message' => 'No se pudo actualizar la configuración'], 500);
            return;
        }

        $this->platformRepository->setEnabled($platform, $enabled);
        $this->reloadEmailPatterns();

        json_response([
            'success' => true,
            'message' => $enabled
                ? "{$displayName} habilitado para lectura de correos"
                : "{$displayName} deshabilitado para lectura de correos",
            'enabled' => $enabled,
        ]);
    }

    private function reloadEmailPatterns(): void
    {
        SettingsRepository::clearCache();
        $filter = new EmailFilterService();
        $filter->reloadPatterns();
    }

    private function getPlatformDisplayName(string $platform): string
    {
        $row = $this->platformRepository->findByName($platform);
        return $row['display_name'] ?? ucfirst($platform);
    }

    private function renderView(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = base_path('views/' . str_replace('.', '/', $view) . '.php');
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            http_response_code(404);
            echo "Vista no encontrada: {$view}";
        }
    }
}
