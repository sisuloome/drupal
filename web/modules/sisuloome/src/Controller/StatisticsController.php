<?php

namespace Drupal\sisuloome\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StatisticsController.
 */
class StatisticsController extends ControllerBase {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * Returns total user count excluding the anonymous one with identifirt of 0.
   *
   * @return int
   *   Total user count
   */
  private function getUserCount() : int {
    return $this
      ->entityTypeManager
      ->getStorage('user')
      ->getQuery()
      ->condition('uid', 0, '<>')
      ->count()
      ->execute();
  }

  /**
   * Returns node types with count of nodes created.
   *
   * @return array
   *   Node types with key as type and count as value
   */
  private function getNodeTypeCounts() : array {
    $query = $this->database->select('node', 'n');
    $query->addField('n', 'type');
    $query->addExpression('COUNT(*)', 'count');
    $query->groupBy('n.type');

    return $query->execute()->fetchAllKeyed();
  }

  /**
   * Statistics page.
   *
   * @return array
   *   Page renderable structure
   */
  public function index() : array {
    $response['users'] = [
      '#prefix' => '<h2>',
      '#suffix' => '</h2>',
      '#plain_text' => $this
        ->t('Total users count: :count', [
          ':count' => $this->getUserCount(),
        ]),
    ];

    $node_types = node_type_get_names();
    $node_type_count = $this->getNodeTypeCounts();

    $response['nodes']['header'] = [
      '#type' => 'markup',
      '#markup' => $this
        ->t('Node count by type'),
      '#prefix' => '<h2>',
      '#suffix' => '</h2>',
    ];
    $response['nodes']['table'] = [
      '#type' => 'table',
      '#header' => [
        $this
          ->t('Name'),
        $this
          ->t('Count'),
      ],
    ];

    if ($node_types) {
      foreach ($node_types as $key => $name) {
        $response['nodes']['table'][$key]['name'] = [
          '#plain_text' => $name,
        ];
        $response['nodes']['table'][$key]['count'] = [
          '#plain_text' => $node_type_count[$key] ?? 0,
        ];
      }

      $response['nodes']['table'][]['total'] = [
        '#prefix' => '<strong>',
        '#suffix' => '</strong>',
        '#plain_text' => $this
          ->t('Total: :count', [
            ':count' => array_sum(array_values($node_type_count)),
          ]),
        '#wrapper_attributes' => [
          'colspan' => 2,
        ]
      ];
    }

    return $response;
  }

}
