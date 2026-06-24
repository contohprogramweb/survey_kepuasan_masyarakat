<?php

namespace App\Services;

use CodeIgniter\Config\BaseService;

/**
 * Service Container untuk Dependency Injection
 * Mengimplementasikan pattern Service Locator dengan Lazy Loading
 */
class Container extends BaseService
{
    /**
     * @var array Registry service yang terdaftar
     */
    protected static array $registry = [];

    /**
     * @var array Instance service yang sudah di-resolve
     */
    protected static array $instances = [];

    /**
     * Daftarkan service ke container
     *
     * @param string $id Identifier service
     * @param callable $concrete Callable untuk membuat instance
     * @param bool $shared Apakah service singleton
     * @return void
     */
    public static function register(string $id, callable $concrete, bool $shared = true): void
    {
        static::$registry[$id] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];
    }

    /**
     * Resolve service dari container
     *
     * @param string $id Identifier service
     * @param array $parameters Parameter tambahan
     * @return mixed
     * @throws \Exception Jika service tidak ditemukan
     */
    public static function resolve(string $id, array $parameters = []): mixed
    {
        if (!isset(static::$registry[$id])) {
            throw new \Exception("Service [{$id}] tidak terdaftar dalam container.");
        }

        // Return shared instance jika ada
        if (static::$registry[$id]['shared'] && isset(static::$instances[$id])) {
            return static::$instances[$id];
        }

        // Resolve service
        $instance = call_user_func_array(
            static::$registry[$id]['concrete'],
            [$parameters]
        );

        // Cache jika shared
        if (static::$registry[$id]['shared']) {
            static::$instances[$id] = $instance;
        }

        return $instance;
    }

    /**
     * Cek apakah service terdaftar
     *
     * @param string $id
     * @return bool
     */
    public static function has(string $id): bool
    {
        return isset(static::$registry[$id]);
    }

    /**
     * Hapus instance cached (untuk testing)
     *
     * @param string|null $id
     * @return void
     */
    public static function forget(?string $id = null): void
    {
        if ($id === null) {
            static::$instances = [];
        } else {
            unset(static::$instances[$id]);
        }
    }

    /**
     * Bind service dengan dependencies otomatis
     *
     * @param string $abstract
     * @param string|null $concrete
     * @param bool $shared
     * @return void
     */
    public static function bind(string $abstract, ?string $concrete = null, bool $shared = true): void
    {
        $concrete = $concrete ?? $abstract;

        static::register($abstract, function ($params) use ($concrete) {
            return static::build($concrete, $params);
        }, $shared);
    }

    /**
     * Build class dengan dependency injection otomatis
     *
     * @param string $class
     * @param array $parameters
     * @return object
     * @throws \ReflectionException
     */
    protected static function build(string $class, array $parameters = []): object
    {
        $reflector = new \ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class [{$class}] tidak dapat di-instantiate.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class;
        }

        $dependencies = $constructor->getParameters();
        $resolvedDeps = [];

        foreach ($dependencies as $dep) {
            $type = $dep->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $resolvedDeps[] = static::resolve($dep->getType()->getName());
            } elseif ($dep->isDefaultValueAvailable()) {
                $resolvedDeps[] = $dep->getDefaultValue();
            } else {
                $resolvedDeps[] = $parameters[$dep->getName()] ?? null;
            }
        }

        return $reflector->newInstanceArgs($resolvedDeps);
    }
}
