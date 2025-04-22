<?php

namespace NyroDev\UtilityBundle\Utility;

use Exception;
use NyroDev\UtilityBundle\Services\ImageService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TinymceBrowser
{
    private bool $searched = false;
    private array $paths;
    private array $files;
    private array $directories;
    private int $fullSize;

    public const EXTENSIONS = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg'],
        'media' => ['mov', 'mpeg', 'mp4', 'm4v', 'avi', 'mpg', 'wma', 'flv', 'webm'],
    ];
    public const UPLOAD_MIME_TYPES = [
        'image' => [
            'image/jpg',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/svg+xml',
        ],
        'media' => [
            'video/quicktime',
            'video/mpeg',
            'video/mp4',
            'video/x-m4v',
            'video/x-msvideo',
            'video/mpg',
            'audio/x-ms-wma',
            'video/x-flv',
            'video/webm',
        ],
    ];

    public function __construct(
        private readonly NyrodevService $nyrodevService,
        private readonly FormFactoryInterface $formFactory,
        private readonly Request $request,
        private readonly string $type,
        private readonly string $dirName = 'tinymce',
    ) {
        $requestPath = $this->request->get('path');
        if (false !== strpos($requestPath, '.')) {
            throw new AccessDeniedException();
        }

        $this->paths = explode('/', $requestPath);
        if (count($this->paths)) {
            $fs = new Filesystem();
            if (!$fs->exists($this->getFullDirPath())) {
                throw new AccessDeniedException();
            }
        }
    }

    public function getUrl(string $type, ?string $value = null): string
    {
        $requestUri = $this->request->getRequestUri();
        switch ($type) {
            case 'upload':
            case 'current':
                return $requestUri;
            case 'createDir':
            case 'path':
            case 'sortBy':
            case 'rename':
            case 'delete':
            case 'confirm':
                $url = parse_url($requestUri);
                $queryStringArr = [];
                if (isset($url['query'])) {
                    parse_str($url['query'], $queryStringArr);
                }

                if ('confirm' !== $type && isset($queryStringArr['action'])) {
                    unset($queryStringArr['action']);
                }

                if (null !== $value) {
                    if ('rename' === $type || 'delete' === $type) {
                        $queryStringArr['action'] = $type;
                        $queryStringArr['name'] = $value;
                    } else {
                        $queryStringArr[$type] = $value;
                    }
                } else {
                    $queryStringArr['action'] = $type;
                }

                $queryStringArr = array_filter($queryStringArr);

                return $url['path'].($queryStringArr ? '?'.http_build_query($queryStringArr) : '');
        }

        return '#';
    }

    public function getQueryStings(): array
    {
        $ret = [];

        $search = [
            'path',
            'sortBy',
            'action',
        ];

        foreach ($search as $s) {
            if ($val = $this->request->query->get($s)) {
                $ret[$s] = $val;
            }
        }

        return $ret;
    }

    public function getUploadAccept(): string
    {
        if (!isset(self::EXTENSIONS[$this->type])) {
            return '';
        }

        return implode(',', array_merge(
            array_map(fn ($ext) => '.'.$ext, self::EXTENSIONS[$this->type]),
            self::UPLOAD_MIME_TYPES[$this->type]
        ));
    }

    private function getPublicDirPath(): string
    {
        return $this->nyrodevService->getParameter('kernel.project_dir').'/public';
    }

    public function getRootFullDirPath(bool $createIfNotExists = true): string
    {
        $fullPath = $this->getPublicDirPath().'/'.$this->dirName;

        if ($createIfNotExists) {
            $fs = new Filesystem();
            if (!$fs->exists($fullPath)) {
                $fs->mkdir($fullPath);
            }
        }

        return $fullPath;
    }

    public function getPath(): string
    {
        return implode('/', $this->paths);
    }

    public function getFullDirPath(): string
    {
        return $this->getRootFullDirPath().(count($this->paths) ? '/'.implode('/', $this->paths) : '');
    }

    public function getReponse(): Response|array
    {
        if ($this->request->query->has('action')) {
            return $this->handleAction();
        } elseif ($this->request->isMethod('post')) {
            return $this->handlePost();
        }

        return [
            'view' => '@NyroDevUtiliy/tinymce/browser.html.php',
            'prm' => [
                'tinymceBrowser' => $this,
            ],
        ];
    }

    private function handlePost(): Response
    {
        if ($this->request->files->has('file')) {
            /** @var UploadedFile */
            $file = $this->request->files->get('file');
            $fullDirPath = $this->getFullDirPath();

            $uniqFileName = $this->nyrodevService->getUniqFileName($fullDirPath, $file->getClientOriginalName());

            $file->move($fullDirPath, $uniqFileName);

            return new Response($uniqFileName);
        }

        throw new Exception('Post not supported');
    }

    private function handleAction(): Response|array
    {
        switch ($this->request->query->get('action')) {
            case 'createDir':
                return $this->handleCreateDir();
            case 'rename':
                return $this->handleRename();
            case 'delete':
                return $this->handleDelete();
        }

        throw new Exception('Action not supported');
    }

    private function handleCreateDir(): Response|array
    {
        if (!$this->canCreateDir()) {
            throw new AccessDeniedException('Cannot create dir');
        }

        $form = $this->createFormBuilder([], [
            'action' => $this->request->getRequestUri(),
        ])
            ->add('name', TextType::class, [
                'label' => $this->nyrodevService->trans('nyrodev.browser.folderName'),
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'placeholder' => $this->nyrodevService->trans('nyrodev.browser.folderName'),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->nyrodevService->trans('nyrodev.browser.createFolder'),
            ])
            ->getForm();

        $newUrl = false;
        $newFodlerName = false;
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newFodlerName = $this->nyrodevService->urlify($data['name']);
            $fs = new Filesystem();

            $newPath = $this->getFullDirPath().'/'.$newFodlerName;
            if (!$fs->exists($newPath)) {
                $fs->mkdir($newPath);
            }

            $newUrl = $this->getUrl('path', trim(implode('/', array_merge($this->paths, [$newFodlerName])), '/'));
        }

        return [
            'view' => '@NyroDevUtiliy/tinymce/createDir.html.php',
            'prm' => [
                'form' => $form->createView(),
                'newUrl' => $newUrl,
                'newFodlerName' => $newFodlerName,
            ],
        ];
    }

    private function handleRename(): Response|array
    {
        $name = $this->request->query->get('name');
        if (!$name) {
            throw new Exception('Name not provided');
        }

        $fullPath = $this->getFullDirPath().'/'.$name;
        $fs = new Filesystem();
        if (!$fs->exists($fullPath)) {
            throw new Exception('Name does not exists.');
        }

        $ext = $this->nyrodevService->getExt($fullPath);
        $useName = str_replace('.'.$ext, '', $name);

        $form = $this->createFormBuilder([], [
            'action' => $this->request->getRequestUri(),
        ])
            ->add('name', TextType::class, [
                'label' => $this->nyrodevService->trans('nyrodev.browser.name'),
                'data' => $useName,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Callback(function (mixed $value, ExecutionContextInterface $context, mixed $payload) use ($useName, $ext, $fs) {
                        if (!$value || $value === $useName) {
                            return;
                        }

                        $newName = $this->nyrodevService->urlify($value);

                        if ($fs->exists($this->getFullDirPath().'/'.$newName.($ext ? '.'.$ext : ''))) {
                            $context->buildViolation($this->nyrodevService->trans('nyrodev.browser.nameAlreadyExists'))
                                ->addViolation()
                            ;
                        }
                    }),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->nyrodevService->trans('nyrodev.browser.rename'),
            ])
            ->getForm();

        $newUrl = null;
        $newName = null;
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newName = $this->nyrodevService->urlify($data['name']);

            $fs->rename(
                $fullPath,
                $this->getFullDirPath().'/'.$newName.($ext ? '.'.$ext : '')
            );

            $newUrl = $this->getUrl('current');
        }

        return [
            'view' => '@NyroDevUtiliy/tinymce/rename.html.php',
            'prm' => [
                'form' => $form->createView(),
                'newUrl' => $newUrl,
                'newName' => $newName,
            ],
        ];
    }

    private function handleDelete(): Response|array
    {
        $name = $this->request->query->get('name');
        if (!$name) {
            throw new Exception('Name not provided');
        }

        $fullPath = $this->getFullDirPath().'/'.$name;
        $fs = new Filesystem();
        if (!$fs->exists($fullPath)) {
            throw new Exception('Name does not exists.');
        }

        $confirmed = false;
        if ($this->request->query->getBoolean('confirm')) {
            $fs->remove($fullPath);
            $confirmed = true;
        }

        return [
            'view' => '@NyroDevUtiliy/tinymce/delete.html.php',
            'prm' => [
                'tinyBrowser' => $this,
                'isDir' => is_dir($fullPath),
                'name' => $name,
                'confirmed' => $confirmed,
            ],
        ];
    }

    protected function createFormBuilder($data = null, array $options = []): FormBuilderInterface
    {
        return $this->formFactory->createBuilder(FormType::class, $data, $options);
    }

    private function doSearch(): void
    {
        if ($this->searched) {
            return;
        }

        $this->searched = true;

        $fullDirPath = $this->getFullDirPath();

        $genericfinder = new Finder();
        $genericfinder
            ->in($fullDirPath)
            ->depth('== 0')
        ;

        switch ($this->getSortBy()) {
            case 'sizeAsc':
                $genericfinder->sortBySize();
                break;
            case 'sizeDesc':
                $genericfinder->sortBySize()->reverseSorting();
                break;
            case 'dateAsc':
                $genericfinder->sortByModifiedTime();
                break;
            case 'dateDesc':
                $genericfinder->sortByModifiedTime()->reverseSorting();
                break;
            case 'nameDesc':
                $genericfinder->sortByCaseInsensitiveName()->reverseSorting();
                break;
        }

        $genericfinder->sortByCaseInsensitiveName();

        $fileFinder = clone $genericfinder;
        $dirFinder = clone $genericfinder;

        $fileFinder->files();
        $dirFinder->directories();

        $nameSearch = '*';
        if ($this->getSearch()) {
            $nameSearch .= $this->getSearch().'*';
            echo $nameSearch;
            $dirFinder->name($nameSearch);
        }

        if (isset(self::EXTENSIONS[$this->type])) {
            $nameSearchTmp = [];
            foreach (self::EXTENSIONS[$this->type] as $ext) {
                $nameSearchTmp[] = $nameSearch.'.'.$ext;
            }
            $nameSearch = $nameSearchTmp;
        }

        $fileFinder->name($nameSearch);

        $this->files = iterator_to_array($fileFinder);
        $this->directories = iterator_to_array($dirFinder);

        $this->fullSize = 0;
        foreach ($this->files as $file) {
            $this->fullSize += $file->getSize();
        }
    }

    public function canUpload(): bool
    {
        // @todo
        return true;
    }

    public function canCreateDir(): bool
    {
        return (bool) $this->nyrodevService->getParameter('nyroDev_utility.browser.allowAddDir');
    }

    public function getSearch(): ?string
    {
        return $this->request->query->get('q');
    }

    public function getSortBy(): string
    {
        return $this->request->query->get('sortBy', 'nameAsc');
    }

    public function getSorts(): array
    {
        return [
            'nameAsc' => $this->nyrodevService->trans('nyrodev.browser.sort.name').' ↓',
            'nameDesc' => $this->nyrodevService->trans('nyrodev.browser.sort.name').' ↑',
            'dateAsc' => $this->nyrodevService->trans('nyrodev.browser.sort.date').' ↓',
            'dateDesc' => $this->nyrodevService->trans('nyrodev.browser.sort.date').' ↑',
            'sizeAsc' => $this->nyrodevService->trans('nyrodev.browser.sort.size').' ↓',
            'sizeDesc' => $this->nyrodevService->trans('nyrodev.browser.sort.size').' ↑',
        ];
    }

    public function getPaths(): array
    {
        return array_slice($this->paths, 0, -1);
    }

    public function getLastPath(): ?string
    {
        if (count($this->paths)) {
            return $this->paths[count($this->paths) - 1];
        }

        return null;
    }

    public function getNbFiles(): int
    {
        $this->doSearch();

        return count($this->files);
    }

    public function getNbDirs(): int
    {
        $this->doSearch();

        return count($this->directories);
    }

    public function getFullSize(): int
    {
        $this->doSearch();

        return $this->fullSize;
    }

    public function getDirectories(): iterable
    {
        $this->doSearch();

        return $this->directories;
    }

    public function getFiles(): iterable
    {
        $this->doSearch();

        return $this->files;
    }

    public function getFileUrl(SplFileInfo $file): string
    {
        $rootRelativePath = str_replace($this->getPublicDirPath(), '', $file->getRealPath());

        return $this->nyrodevService->getUrl(substr($rootRelativePath, 1));
    }

    public function getResizeFileUrl(SplFileInfo $file): string
    {
        return $this->nyrodevService->get(ImageService::class)->resize($file->getRealPath(), [
            'name' => 'tinyBrowser',
            'w' => 200,
            'h' => 200,
            'useMaxResize' => true,
        ]);
    }
}
