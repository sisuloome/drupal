<?php

namespace Drupal\sisuloome\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\user\Entity\User;

/**
 * Provides a resource to get current user data.
 *
 * @RestResource(
 *   id = "sisuloome_user_resource",
 *   label = @Translation("Sisuloome User resource"),
 *   uri_paths = {
 *     "canonical" = "api/sisuloome/user"
 *   }
 * )
 */
class UserResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory')->get('la_pills_rest');
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
    if (!$this->currentUser->isAuthenticated()) {
      throw new AccessDeniedHttpException();
    }

    $data = [
      'id' => (int)$this->currentUser->id(),
      'name' => $this->currentUser->getAccountName(),
      'display_name' => $this->currentUser->getDisplayName(),
      'mail' => $this->currentUser->getEmail(),
    ];

    $response = new ResourceResponse($data, 200);

    // TODO See if setting it non-cacheable is a better approach, see URL for details
    // https://drupal.stackexchange.com/a/224508/92770
    $response->addCacheableDependency($data);

    return $response;
  }

}
