<?php

namespace NyroDev\UtilityBundle\Utility\Menu;

use Closure;
use InvalidArgumentException;

abstract class Menuable
{
    protected ?Menuable $parent = null;

    protected ?string $id = null;

    protected ?string $beforeChildsTemplate = null;

    /**
     * @var array<string, mixed>
     */
    protected array $outerAttrs = [];

    /**
     * @var array<string, Menuable>
     */
    protected array $childs = [];

    /**
     * @var array<string, mixed>
     */
    protected array $childOuterAttrs = [];

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getComputedId(): string
    {
        return $this->id ?? 'menu-obj-'.spl_object_id($this);
    }

    public function getBeforeChildsTemplate(): ?string
    {
        if (null !== $this->beforeChildsTemplate) {
            return $this->beforeChildsTemplate;
        }

        return $this->getParent() ? $this->getParent()->getBeforeChildsTemplate() : '@NyroDevUtility/Menu/_beforeChilds.html.php';
    }

    public function setBeforeChildsTemplate(?string $beforeChildsTemplate): self
    {
        $this->beforeChildsTemplate = $beforeChildsTemplate;

        return $this;
    }

    /**
     * Attributes applied to the li.
     *
     * @return array<string, string>
     */
    public function getOuterAttrs(): array
    {
        $outerAttrs = $this->outerAttrs;

        $outerAttrs['class'] = ($outerAttrs['class'] ?? '').' menu-level-'.$this->getLevel().' menu-'.$this->getType();

        if ($this->hasChilds()) {
            $outerAttrs['class'] .= ' menu-has-childs';
        }

        if ($this->isActiveOrChildActive()) {
            $outerAttrs['class'] .= ' menu-active';
        }

        return $outerAttrs;
    }

    /**
     * @param array<string, mixed> $outerAttrs
     */
    public function setOuterAttrs(array $outerAttrs): self
    {
        $this->outerAttrs = $outerAttrs;

        return $this;
    }

    public function addOuterAttr(string $key, mixed $value): self
    {
        $this->outerAttrs[$key] = $value;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->getParent() ? $this->getParent()->getLevel() + 1 : 0;
    }

    protected function getParent(): ?Menuable
    {
        return $this->parent;
    }

    protected function setParent(Menuable $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function isActiveOrChildActive(): bool
    {
        if ($this instanceof MenuActivableInterface && $this->isActive()) {
            return true;
        }
        foreach ($this->childs as $child) {
            if ($child->isActiveOrChildActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Child menus.
     *
     * @return array<string, Menuable>
     */
    public function getChilds(): array
    {
        return $this->childs;
    }

    public function hasChilds(): bool
    {
        return !empty($this->childs);
    }

    public function getChild(string $name): ?Menuable
    {
        return $this->childs[$name] ?? null;
    }

    public function removeChild(string $name): self
    {
        if (isset($this->childs[$name])) {
            unset($this->childs[$name]);
        }

        return $this;
    }

    /**
     * @param array<string, Menuable> $childs
     */
    public function setChilds(array $childs): self
    {
        foreach ($childs as $name => $child) {
            if (!$child instanceof Menuable) {
                throw new InvalidArgumentException('Childs must be instances of Menuable');
            }
            $this->addChild($name, $child);
        }

        return $this;
    }

    public function addChild(string $name, Menuable $child): self
    {
        $child->setParent($this);
        $this->childs[$name] = $child;

        return $this;
    }

    public function reorderChilds(Closure|array $reorder): self
    {
        if (is_array($reorder)) {
            $reorder = function (string $a, string $b) use ($reorder): int {
                $posA = array_search($a, $reorder, true);
                $posB = array_search($b, $reorder, true);

                if (false === $posA && false === $posB) {
                    return 0; // Keep original order if not found
                } elseif (false === $posA) {
                    return 1; // $a comes after $b
                } elseif (false === $posB) {
                    return -1; // $b comes after $a
                }

                return $posA <=> $posB;
            };
        }

        uksort($this->childs, $reorder);

        return $this;
    }

    /**
     * Attributes applied to the child's ul.
     *
     * @return array<string, string>
     */
    public function getChildOuterAttrs(): array
    {
        return $this->childOuterAttrs;
    }

    /**
     * @param array<string, mixed> $childOuterAttrs
     */
    public function setChildOuterAttrs(array $childOuterAttrs): self
    {
        $this->childOuterAttrs = $childOuterAttrs;

        return $this;
    }

    public function addChildOuterAttr(string $key, mixed $value): self
    {
        $this->childOuterAttrs[$key] = $value;

        return $this;
    }

    public function getType(): string
    {
        $class = explode('\\', static::class);

        return strtolower(array_pop($class));
    }
}
