# Multi-Tenancy Bundle

The Multi-Tenancy Bundle will manage the multi-tenancy for your Symfony application.  
It will allow you to have multiple tenants in your application and manage them easily.  

The tenancy currently supports the following features:
- Database Configuration per Tenant

## Installation

```bash
composer require norvutec/multi-tenancy
```

### Doctrine Configuration

```yaml
doctrine:
    dbal:
        default_connection: default
        connections:
            default:
                url: '%env(resolve:DATABASE_URL)%'
                driver: 'pdo_mysql'
                server_version: '5.7'
                charset: utf8mb4
            tenant:
                driver: 'pdo_mysql'
                server_version: '5.7'
                charset: utf8mb4
                url: '%env(resolve:DATABASE_TENANT_URL)%'
                wrapper_class: NorvuTec\MultiTenancyBundle\Doctrine\DBAL\TenantConnection

    orm:
        default_entity_manager: default
        entity_managers:
            default:
                connection: default
                mappings:
                    Main:
                        is_bundle: false
                        type: annotation
                        dir: '%kernel.project_dir%/src/Entity/Main'
                        prefix: 'App\Entity\Main'
                        alias: Main
                    MultiTenancyBundle:
                        is_bundle: true
                        type: annotation
                        dir: 'Entity'
                        prefix: 'MultiTenancyBundle\Entity'
                        alias: MultiTenant
            tenant:
                connection: tenant
                mappings:
                    Tenant:
                        is_bundle: false
                        type: annotation
                        dir: '%kernel.project_dir%/src/Entity/Tenant'
                        prefix: 'App\Entity\Tenant'
                        alias: Tenant
```