<?php

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UserPolicy', function () {
    
    let($policy);

    beforeEach(function () {
        $policy = new UserPolicy();
    });

    describe('view', function () {
        
        it('allows viewing public profiles', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create(['is_private' => false]);

            expect($policy->view($viewer, $profile))->toBeTrue();
        });

        it('allows viewing own profile', function () {
            $user = User::factory()->create(['is_private' => true]);

            expect($policy->view($user, $user))->toBeTrue();
        });

        it('allows viewing own private profile', function () {
            $user = User::factory()->create(['is_private' => true]);

            expect($policy->view($user, $user))->toBeTrue();
        });

        it('prevents viewing private profiles by others', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create(['is_private' => true]);

            expect($policy->view($viewer, $profile))->toBeFalse();
        });
    });

    describe('edit', function () {
        
        it('allows editing own profile', function () {
            $user = User::factory()->create();

            expect($policy->edit($user, $user))->toBeTrue();
        });

        it('prevents editing other profiles', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create();

            expect($policy->edit($viewer, $profile))->toBeFalse();
        });
    });

    describe('updateSettings', function () {
        
        it('allows updating own settings', function () {
            $user = User::factory()->create();

            expect($policy->updateSettings($user, $user))->toBeTrue();
        });

        it('prevents updating other users settings', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create();

            expect($policy->updateSettings($viewer, $profile))->toBeFalse();
        });
    });

    describe('changePassword', function () {
        
        it('allows changing own password', function () {
            $user = User::factory()->create();

            expect($policy->changePassword($user, $user))->toBeTrue();
        });

        it('prevents changing other users passwords', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create();

            expect($policy->changePassword($viewer, $profile))->toBeFalse();
        });
    });

    describe('updatePrivacy', function () {
        
        it('allows updating own privacy settings', function () {
            $user = User::factory()->create();

            expect($policy->updatePrivacy($user, $user))->toBeTrue();
        });

        it('prevents updating other users privacy settings', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create();

            expect($policy->updatePrivacy($viewer, $profile))->toBeFalse();
        });
    });

    describe('uploadPhotos', function () {
        
        it('allows uploading photos for own profile', function () {
            $user = User::factory()->create();

            expect($policy->uploadPhotos($user, $user))->toBeTrue();
        });

        it('prevents uploading photos for other profiles', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create();

            expect($policy->uploadPhotos($viewer, $profile))->toBeFalse();
        });
    });

    describe('deletePhotos', function () {
        
        it('allows deleting photos from own profile', function () {
            $user = User::factory()->create();

            expect($policy->deletePhotos($user, $user))->toBeTrue();
        });

        it('prevents deleting photos from other profiles', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create();

            expect($policy->deletePhotos($viewer, $profile))->toBeFalse();
        });
    });

    describe('delete', function () {
        
        it('allows deleting own account', function () {
            $user = User::factory()->create();

            expect($policy->delete($user, $user))->toBeTrue();
        });

        it('prevents deleting other accounts', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create();

            expect($policy->delete($viewer, $profile))->toBeFalse();
        });
    });

    describe('viewPublic', function () {
        
        it('allows viewing public profiles', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create(['is_private' => false]);

            expect($policy->viewPublic($viewer, $profile))->toBeTrue();
        });

        it('allows viewing own private profile via viewPublic', function () {
            $user = User::factory()->create(['is_private' => true]);

            expect($policy->viewPublic($user, $user))->toBeTrue();
        });

        it('prevents viewing other private profiles via viewPublic', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create(['is_private' => true]);

            expect($policy->viewPublic($viewer, $profile))->toBeFalse();
        });
    });
});
