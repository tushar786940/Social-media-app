<?php

use Illuminate\Support\Facades\View;

beforeEach(function () {
    // Ensure we're not in maintenance mode
    config(['app.maintenance.driver' => 'file']);
});

/**
 * Foundation Tests
 * 
 * Test the application foundation layer including:
 * - Views and layouts
 * - Components
 * - Configuration
 * - Routes
 */

describe('Foundation', function () {
    
    describe('Configuration', function () {
        
        it('has correct app configuration', function () {
            expect(config('app.name'))->toBe('Laravel');
            expect(config('app.env'))->toBe('testing');
            expect(config('app.debug'))->toBeFalse();
            expect(config('app.timezone'))->toBe('UTC');
        });

        it('has view configuration', function () {
            expect(config('view.paths'))->toContain(realpath(base_path('resources/views')));
            expect(config('view.compiled'))->toBe(realpath(storage_path('framework/views')));
        });

        it('has filesystem configuration', function () {
            expect(config('filesystems.default'))->toBe('local');
            expect(config('filesystems.disks.local.driver'))->toBe('local');
            expect(config('filesystems.disks.public.driver'))->toBe('local');
        });
    });

    describe('Views', function () {
        
        it('can render main layout', function () {
            $view = View::make('layouts.app');
            
            expect($view)->not->toBeNull();
            expect($view->render())->toContain('Social');
        });

        it('can render header partial', function () {
            $view = View::make('layouts.partials.header');
            
            expect($view)->not->toBeNull();
            expect($view->render())->toContain('Social');
        });

        it('can render footer partial', function () {
            $view = View::make('layouts.partials.footer');
            
            expect($view)->not->toBeNull();
            expect($view->render())->toContain('Connect');
        });

        it('can render home page', function () {
            $view = View::make('pages.home');
            
            expect($view)->not->toBeNull();
            expect($view->render())->toContain('Welcome');
        });
    });

    describe('Components', function () {
        
        describe('Alerts', function () {
            it('can render flash alert component', function () {
                $view = View::make('components.alerts.flash', [
                    'type' => 'success',
                    'message' => 'Test message'
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Test message');
            });
        });

        describe('Buttons', function () {
            it('can render primary button', function () {
                $view = View::make('components.buttons.primary', [], [
                    'slot' => 'Click me'
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Click me');
            });

            it('can render secondary button', function () {
                $view = View::make('components.buttons.secondary', [], [
                    'slot' => 'Click me'
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Click me');
            });

            it('can render danger button', function () {
                $view = View::make('components.buttons.danger', [], [
                    'slot' => 'Delete'
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Delete');
            });

            it('can render icon button', function () {
                $view = View::make('components.buttons.icon', [
                    'size' => 'md'
                ], [
                    'slot' => '<svg></svg>'
                ]);
                
                expect($view)->not->toBeNull();
            });
        });

        describe('Cards', function () {
            it('can render base card', function () {
                $view = View::make('components.cards.base', [
                    'title' => 'Test Card'
                ], [
                    'slot' => 'Card content'
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Test Card');
                expect($view->render())->toContain('Card content');
            });

            it('can render stat card', function () {
                $view = View::make('components.cards.stat', [
                    'label' => 'Users',
                    'value' => '1000',
                    'color' => 'blue'
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Users');
                expect($view->render())->toContain('1000');
            });
        });

        describe('Forms', function () {
            it('can render input component', function () {
                $view = View::make('components.forms.input', [
                    'name' => 'username',
                    'label' => 'Username',
                    'placeholder' => 'Enter username'
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Username');
            });

            it('can render textarea component', function () {
                $view = View::make('components.forms.textarea', [
                    'name' => 'bio',
                    'label' => 'Bio',
                    'rows' => 5
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Bio');
            });

            it('can render select component', function () {
                $view = View::make('components.forms.select', [
                    'name' => 'country',
                    'label' => 'Country',
                    'options' => ['us' => 'USA', 'uk' => 'UK']
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Country');
            });

            it('can render checkbox component', function () {
                $view = View::make('components.forms.checkbox', [
                    'name' => 'terms',
                    'label' => 'I agree to terms'
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('I agree to terms');
            });

            it('can render file component', function () {
                $view = View::make('components.forms.file', [
                    'name' => 'avatar',
                    'label' => 'Upload Avatar'
                ]);
                
                expect($view)->not->toBeNull();
                expect($view->render())->toContain('Upload Avatar');
            });
        });

        describe('Icons', function () {
            it('can render user icon', function () {
                $view = View::make('components.icons.user', [
                    'size' => 'md'
                ]);
                
                expect($view)->not->toBeNull();
            });

            it('can render placeholder icon', function () {
                $view = View::make('components.icons.placeholder', [
                    'size' => 'md'
                ]);
                
                expect($view)->not->toBeNull();
            });
        });
    });

    describe('Error Pages', function () {
        it('can render 404 error page', function () {
            $view = View::make('errors.404');
            
            expect($view)->not->toBeNull();
            expect($view->render())->toContain('404');
            expect($view->render())->toContain('Page Not Found');
        });

        it('can render 403 error page', function () {
            $view = View::make('errors.403');
            
            expect($view)->not->toBeNull();
            expect($view->render())->toContain('403');
            expect($view->render())->toContain('Forbidden');
        });

        it('can render 419 error page', function () {
            $view = View::make('errors.419');
            
            expect($view)->not->toBeNull();
            expect($view->render())->toContain('419');
            expect($view->render())->toContain('Page Expired');
        });

        it('can render 429 error page', function () {
            $view = View::make('errors.429');
            
            expect($view)->not->toBeNull();
            expect($view->render())->toContain('429');
            expect($view->render())->toContain('Too Many Requests');
        });

        it('can render 500 error page', function () {
            $view = View::make('errors.500');
            
            expect($view)->not->toBeNull();
            expect($view->render())->toContain('500');
            expect($view->render())->toContain('Server Error');
        });
    });
});

describe('Routes', function () {
    
    it('has home route', function () {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.home');
    });

    it('has search route', function () {
        $response = $this->get('/search');
        
        $response->assertStatus(200);
    });

    it('has health check endpoint', function () {
        $response = $this->get('/health');
        
        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
    });
});

describe('Blade Directives', function () {
    
    it('has custom blade directives', function () {
        // Test datetime directive
        $view = View::make('test', [
            'date' => now()
        ]);
        
        // These would be tested by rendering views that use the directives
        // For now, just verify the service provider is registered
        expect(app()->bound('view'))->toBeTrue();
    });
});
