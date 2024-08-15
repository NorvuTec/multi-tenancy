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
              charset: utf8mb4
              server_version: '10.6.16-MariaDB'
              profiling_collect_backtrace: '%kernel.debug%'
              use_savepoints: true
          tenant:
            url: '%env(resolve:DATABASE_URL)%'
            driver: 'pdo_mysql'
            charset: utf8mb4
            server_version: '10.6.16-MariaDB'
            profiling_collect_backtrace: '%kernel.debug%'
            use_savepoints: true
            wrapper_class: Norvutec\MultiTenancyBundle\Doctrine\DBAL\TenantConnection

    orm:
        default_entity_manager: default
        entity_managers:
            default:
                connection: default
                mappings:
                  System:
                      is_bundle: false
                      type: attribute
                      dir: '%kernel.project_dir%/src/Entity/System'
                      prefix: 'App\Entity\System'
                      alias: System
            tenant:
                connection: tenant
                mappings:
                  Tenant:
                    is_bundle: false
                    type: attribute
                    dir: '%kernel.project_dir%/src/Entity/Tenant'
                    prefix: 'App\Entity\Tenant'
                    alias: Tenant
```

### Multi-Tenancy Configuration
Create File config/packages/multi_tenancy.yaml
```yaml
multi_tenancy:
  tenant_class: App\Entity\System\Tenant ## Class Implementing Tenant Interface
  tenant_select_route: app_test2 ## Route for Tenant Selection redirection
  tenant_migration_config: 'config/migrations/tenant.yaml' ## Migration Configuration for Tenant
```

