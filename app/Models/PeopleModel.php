<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\UserProfileStructure;
use \Gumlet\ImageResize;
use Rogervila\ArrayDiffMultidimensional;
use App\Libraries\SystemSettingsWrapper;
use CodeIgniter\Shield\Entities\User;

class PeopleModel extends Model
{
  // Basic model properties
  protected $DBGroup      = 'default';
  protected $table      = 'people';
  protected $primaryKey     = 'id';
  protected $useAutoIncrement = true;
  protected $returnType     = 'array';
  protected $useSoftDeletes   = false;
  protected $protectFields  = false;
  protected $allowedFields  = [];

  // Dates
  protected $useTimestamps = true;
  protected $dateFormat  = 'datetime';
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
  protected $deletedField  = 'deleted_at';

  // Validation
  protected $validationRules    = [];
  protected $validationMessages   = [];
  protected $skipValidation     = false;
  protected $cleanValidationRules = true;

  // Callbacks
  protected $allowCallbacks = true;
  protected $beforeInsert   = [];
  protected $afterInsert  = [];
  protected $beforeUpdate   = [];
  protected $afterUpdate  = [];
  protected $beforeFind   = [];
  protected $afterFind    = [];
  protected $beforeDelete   = [];
  protected $afterDelete  = [];

  const BASE_FIELDS = [
    'first_name', 'middle_name', 'last_name', 'public_profile', 'email',
    'prefix', 'preferred_pronouns', 'mobile_phone_number', 'reintake',
  ];

  const AUDIT_SKIP_FIELDS = [
    'updated_at',
  ];

  const REVIEW_TRIGGER_FIELDS = [
    'bio', 'email', 'website_link', 'twitter_link', 'mastodon_link',
    'linkedin_link', 'instagram_link', 'facebook_link', 'snapchat_link',
  ];

  const REINTAKE_STATUS_NOT_REQUIRED = 'not_required';
  const REINTAKE_STATUS_REQUIRED = 'required';
  const REINTAKE_STATUS_ACCEPTED = 'accepted';
  const REINTAKE_STATUS_DENIED = 'denied';

  /**
   * Get people with custom fields.
   *
   * @param array $extraConditions Additional conditions to apply
   * @param array $likeConditions Conditions to apply to LIKE
   * @param array $whereInConditions Conditions to apply to WHERE IN
   * @param array $filters Filters to apply
   * @return array List of processed people
   */
  public function getPeopleWithCustomFields(
    array $extraConditions = [],
    array $likeConditions = [],
    array $whereInConditions = [],
    array $filters = []
  ): array {
    $people = $this->getPeople($extraConditions, $likeConditions, $whereInConditions, $filters);
    $this->processData($people);

    return $people;
  }

  /**
   * Get people with custom fields.
   *
   * @param array $extraConditions Additional conditions to apply
   * @param array $likeConditions Conditions to apply to LIKE
   * @param array $whereInConditions Conditions to apply to WHERE IN
   * @param array $filters Filters to apply
   * @return array List of people
   */
  private function getPeople(
    array $extraConditions = [],
    array $likeConditions = [],
    array $whereInConditions = [],
    array $filters = []
  ): array {
    $builder = $this->db->table('people');

    $builder
      ->select('
        people.id,
        people.first_name,
        people.middle_name,
        people.last_name,
        people.email,
        people.mobile_phone_number,
        people.image_url,
        people.preferred_pronouns,
        people.user_id,
        people.public_profile,
        people.created_at,
        people.updated_at,
        users.status as active,
        json_agg(
          json_build_object(
            \'input_type\',
            custom_fields.input_type,
            \'value\',
            custom_field_data.value,
            \'value_json\',
            custom_field_data.value_json,
            \'machine_name\',
            custom_fields.machine_name,
            \'parent_field_value_index\',
            custom_field_data.parent_field_value_index
          )
        ) AS custom_fields
      ')
      ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
      ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
      ->join('users', 'users.id = people.user_id', 'left')
      ->groupStart()
        ->where('custom_fields.model_name', 'People')
        ->orWhere('custom_fields.model_name IS NULL')
      ->groupEnd()
      ->where('users.id IS NOT NULL');

    if (!empty($extraConditions)) {
      foreach ($extraConditions as $key => $value) {
        if (is_array($value)) {
          $builder->whereIn($key, $value);
          continue;
        } else {
          $builder->where($key, $value);
        }
      }
    }

    if (!empty($likeConditions)) {
      $builder->like($likeConditions);
    }

    if (!empty($whereInConditions)) {
      foreach ($whereInConditions as $column => $values) {
        if (!is_array($values) || empty($values)) {
          continue;
        }
        $builder->whereIn($column, $values);
      }
    }

    $builder->groupBy('people.id, users.status');
    $this->applyFilters($builder, $filters);

    $people = $builder->get()->getResultArray();

    return $people;
  }

  /**
   * Apply filters to the query builder.
   *
   * @param object $builder
   * @param array $filters
   * @return void
   */
  private function applyFilters($builder, array $filters) {
    if (empty($filters)) {
      return;
    }

    $anyWithValues = false;
    foreach ($filters as $filterValues) {
      if (!empty($filterValues)) {
        $anyWithValues = true;
        break;
      }
    }

    if ($anyWithValues === false) {
      return;
    }

    $filtersMachineNames = array_keys($filters);
    $dbFields = $this->db->table('custom_fields')
      ->whereIn('machine_name', $filtersMachineNames)
      ->get()->getResultArray();

    $havingFilters = [];
    foreach ($filters as $filterKey => $filterValues) {
      $fieldDb = current(
        array_filter($dbFields, function ($dbField) use ($filterKey) {
          return $dbField['machine_name'] === $filterKey;
        })
      );

      if ($fieldDb === false || empty($filterValues)) {
        continue;
      }

      $jsonValues = json_encode($filterValues);

      switch ($fieldDb['input_type']) {
        case 'short_text':
          $joinedInValues = implode(', ', array_map(function($filterValue) {
            return "'" . addslashes($filterValue) . "'";
          }, $filterValues));
          $havingFilters[] = "MAX(CASE WHEN \"custom_fields\".\"machine_name\" = '{$filterKey}' AND (custom_field_data.value ILIKE ANY(ARRAY[{$joinedInValues}])) THEN 1 ELSE 0 END) = 1";
          break;
        case 'tags':
          $havingFilters[] = "bool_and( CASE WHEN \"custom_fields\".\"machine_name\" = '{$filterKey}' THEN lower(custom_field_data.value_json::text)::jsonb @> lower('{$jsonValues}')::jsonb END)";
          break;
        case 'tags_range':
          if (
            (isset($filterValues['tags']) && count($filterValues['tags'])) ||
            isset($filterValues['from']) ||
            isset($filterValues['to'])
          ) {
            $tagHavingFilterStart = "WHEN \"custom_fields\".\"machine_name\" = '{$filterKey}' THEN";
            $tagHavingFilterParts = [];

            // Handle tags filtering
            if (isset($filterValues['tags']) && count($filterValues['tags'])) {
              foreach ($filterValues['tags'] as $value) {
                $tagHavingFilterParts[] = "
                  EXISTS (
                    SELECT 1
                    FROM jsonb_array_elements(custom_field_data.value_json) AS elem
                    WHERE (lower(elem::text)::jsonb)->'tags' @> lower('[\"{$value}\"]')::jsonb
                )";
              }
            }

            // Handle from/to filtering; can work independently from tags
            $dateFrom = isset($filterValues['from']) ? strtotime($filterValues['from']) : null;
            $dateTo = isset($filterValues['to']) ? strtotime($filterValues['to']) : null;

            $dateConditions = [];

            if ($dateFrom) {
              $dateConditions[] = "(COALESCE((lower(elem::text)::jsonb)->>'from', '0')::bigint >= {$dateFrom})";
            }
            if ($dateTo) {
              $dateConditions[] = "(COALESCE((lower(elem::text)::jsonb)->>'to', '0')::bigint <= {$dateTo})";
            }

            if (!empty($dateConditions)) {
              $tagHavingFilterParts[] = "
                EXISTS (
                  SELECT 1
                  FROM jsonb_array_elements(custom_field_data.value_json) AS elem
                  WHERE " . implode(' AND ', $dateConditions) . "
              )";
            }

            // Combine conditions if any found
            if (!empty($tagHavingFilterParts)) {
              $tagHavingFilterPartsTogether = implode(' AND ', $tagHavingFilterParts);
              $havingFilters[] = "bool_and( CASE {$tagHavingFilterStart} {$tagHavingFilterPartsTogether} END)";
            }
          }
          break;
      }
    }

    if (empty($havingFilters) === false) {
      $havingFiltersJoined = join(' AND ', $havingFilters);
      $builder->having($havingFiltersJoined);
    }
  }

  /**
   * Process people data.
   *
   * @param array $people
   * @return void
   */
  private function processData(array &$people) {
    foreach ($people as &$personData) {
      $personData['custom_fields'] = json_decode($personData['custom_fields'], true);

      foreach ($personData['custom_fields'] as &$customField) {
        $value = $customField['value'];

        if (in_array($customField['input_type'], ['tags_range', 'tags'])) {
          $value = $customField['value_json'];
        }

        if ($customField['input_type'] === 'long_text') {
          $value = nl2br($value);
        }

        $personData[$customField['machine_name']] = $value;
      }

      unset($personData['custom_fields']);
      $personData['image_url'] = $personData['image_url'] ? "profile_images/{$personData['image_url']}" : '';

      $personData['active'] = $personData['active'] !== 'banned';

      // Filter out future active affiliation for non-admin users
      if (php_sapi_name() !== 'cli' && auth()->user()->can('admin.access') === false && isset($personData['activeAffiliation']) && count($personData['activeAffiliation']) > 0) {
        $personData['activeAffiliation'] = array_filter($personData['activeAffiliation'], function ($affiliation) {
          return $affiliation['from'] < time();
        });
      }
    }
  }

  /**
   * Index a single person to Elasticsearch.
   *
   * @param int $personId The ID of the person
   * @param array $data The data of the person
   * @param bool $isUpdate Whether this is an update operation
   * @return bool
   */
  public function indexInSearchIndex($personId, $data, $isUpdate = false) : bool
  {
    $elasticClient = service('elasticsearchClient');

    $searchContent = array_map(function ($field) {
      if (is_array($field)) {
        return json_encode($field);
      }

      return $field;
    }, $data);

    $searchContent = strtolower(
      str_replace(
        ['"', '[', ']', '{', '}'],
        '',
        join(' ', $searchContent),
      )
    );

    return $elasticClient->indexDocument($this->getSearchIndexName(), $personId, [
      'id' => $personId,
      'search_content' => $searchContent,
      'created_at' => $data['created_at'],
    ], $isUpdate);
  }

  /**
   * Sync upserts to Elasticsearch after an insert.
   *
   * @param int $id The ID of the person
   * @param array $data The data of the person
   * @return bool
   */
  public function syncToElasticsearchAfterInsert(int $personId, array $data) : bool
  {
    return $this->indexInSearchIndex($personId, $data);
  }
  
  /**
   * Sync upserts to Elasticsearch after an update.
   *
   * @param int $id The ID of the person
   * @param array $data The data of the person
   * @return bool
   */
  public function syncToElasticsearchAfterUpdate(int $personId, array $data) : bool
  {
    return $this->indexInSearchIndex($personId, $data, true);
  }

  /**
   * Remove document from Elasticsearch after a delete.
   *
   * @param int $id The ID of the person
   * @return bool
   */
  public function removeFromSearchIndex(int $id) : bool
  {
    $esService = service('elasticsearchClient');

    return $esService->deleteDocument($this->getSearchIndexName(), $id);
  }

  /**
   * Generate the Elasticsearch index name based on the environment.
   *
   * @return string
   */
  public function getSearchIndexName(): string
  {
    $env = ENVIRONMENT;

    return "commuse_{$env}_people";
  }

    /**
   * Clears the people cache.
   *
   * @return void
   */
  public function clearPeopleCache($userProfileDataId = null) {
    $cache = \Config\Services::cache();
    $cache->delete('filters_with_values');
    $cachePeopleSearchPath = ROOTPATH . 'writable/cache/people_*';
    exec("rm {$cachePeopleSearchPath} > /dev/null 2> /dev/null");

    if ($userProfileDataId) {
      $this->clearUserCache($userProfileDataId);
    }
  }

  /** 
   * Clears the cache for a specific user.
   * 
   * @param int $userId The ID of the user whose cache is to be cleared.
   * @return void
   */
  public function clearUserCache($userId) {
    $cache = \Config\Services::cache();
    $cache->delete("person_{$userId}");
  }

    /**
   * Retrieves the profile data of a user.
   *
   * @param int $id      The ID of the user.
   *
   * @return array|null  The profile data of the user or null if not found.
   */
  public function getProfileData($id) {
    $db = \Config\Database::connect();
    $builder = $db->table('people');
    $userData = [];

    $baseUserData = $builder
      ->select('people.*')
      ->where('people.user_id', $id)
      ->get()
      ->getRowArray();

    if ($baseUserData === null) {
      return null;
    }

    if ($baseUserData !== null) {
      $userData = array_merge($userData, $baseUserData);

      $customFieldsData = $builder
        ->select('
          custom_field_data.value,
          custom_field_data.value_json,
          custom_fields.machine_name,
          custom_fields.input_type,
          custom_field_data.parent_field_value_index,
          custom_fields.metadata
        ')
        ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
        ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
        ->where('custom_fields.model_name = \'People\'')
        ->where('people.user_id', $id)
        ->get()
        ->getResultArray();

      $userCustomFields = [];
      foreach ($customFieldsData as $customFieldRecord) {
        $value = $customFieldRecord['value'];
        if (in_array($customFieldRecord['input_type'], ['tags_range', 'tags'])) {
          $value = json_decode($customFieldRecord['value_json'], true);
        }

        if ($customFieldRecord['input_type'] === 'multi') {
          $multiValues = [];
          $fieldMetadata = json_decode($customFieldRecord['metadata'], true);
          $childFieldsIds = array_map(fn($childField) => $childField['id'], $fieldMetadata['childFields']);
          $customFieldsModel = model('CustomFieldModel');
          $customFields = $customFieldsModel
            ->select('id, machine_name, input_type')
            ->whereIn('id', $childFieldsIds)
            ->findAll();

          foreach ($customFields as $customField) {
            $childFieldValues = array_filter($customFieldsData, fn($customFieldData) => $customFieldData['machine_name'] === $customField['machine_name'] && $customFieldData['parent_field_value_index'] !== null);

            foreach ($childFieldValues as $childFieldValue) {
              if (isset($multiValues[$childFieldValue['parent_field_value_index']]) === false) {
                $multiValues[$childFieldValue['parent_field_value_index']] = [];
              }

              if ($customField['input_type'] === 'tags') {
                $multiValues[$childFieldValue['parent_field_value_index']][$childFieldValue['machine_name']] = json_decode($childFieldValue['value_json']);
              } else {
                $multiValues[$childFieldValue['parent_field_value_index']][$childFieldValue['machine_name']] = $childFieldValue['value'];
              }
            }
          }

          $value = $multiValues;
        }

        $userCustomFields[$customFieldRecord['machine_name']] = $value;
      }

      $userData = array_merge($userData, $userCustomFields);
    }

    // Filter out future active affiliation for non-admin users
    if (php_sapi_name() !== 'cli' && auth()->user()->can('admin.access') === false && isset($userData['activeAffiliation']) && count($userData['activeAffiliation']) > 0) {
      $userData['activeAffiliation'] = array_filter($userData['activeAffiliation'], function ($affiliation) {
        return $affiliation['from'] < time();
      });
    }

    $users = auth()->getProvider();
    $user = $users->findById($id);
    $userData['active'] = $user->status !== 'banned';

    return $userData;
  }

  /**
   * Retrieves the structure of the user profile.
   *
   * @return array  The structure of the user profile.
   */
  public function getProfileStructure() {
    $userProfileStructure = new UserProfileStructure();
    return $userProfileStructure->getProfileStructure();
  }

  /**
   * Saves profile data to the database.
   *
   * @param array $requestData  The data containing profile fields to be saved.
   * @param int   $userId       The ID of the user whose profile is being saved (optional).
   * @param bool  $sync         Whether to synchronize the user data with the remote service (optional).
   * @param bool  $noSync       Whether to skip synchronization with the remote service (optional).
   * @param bool  $newUser      Whether the user is new (optional).
   *
   * @return array              An array containing transaction status and a response message.
   */
  public function saveProfileData($requestData, $userId = null, $sync = false, $noSync = false, $newUser = false) {
    $peopleModel = new PeopleModel();

    // Regular user editing
    if (is_null($userId)) {
      $userId = auth()->id();
    }

    // Edit other user profile as admin
    if ((php_sapi_name() === 'cli' || (auth()->user() && auth()->user()->can('admin.access') === true)) && isset($requestData['user_id']) && $requestData['user_id']) {
      $userId = $requestData['user_id'];
    }

    $userId = intval($userId);

    if ($newUser) {
      $usersProvider = auth()->getProvider();

      $user = $usersProvider->findByCredentials(['email' => $requestData['email']]);
      if ($user) {
        return [false, 'User with this email already exists'];
      }

      $usersProvider = auth()->getProvider();

      $newUserData  = [
        'username' => substr(md5((string)mt_rand()), 0, 10) . substr(md5($requestData['email']), 0, 20),
        'email'    => $requestData['email'],
        'password' => bin2hex(random_bytes(10)),
        'active'   => true,
      ];

      $user = new User($newUserData);
      $usersProvider->save($user);
      $userId = $usersProvider->getInsertID();
      $userSaved = $usersProvider->findById($userId);
      $userSaved->forcePasswordReset();
    }

    $existingPerson = $peopleModel->where('user_id', $userId)->first();

    if ($existingPerson) {
      $oldProfileData = $this->getProfileData($userId);
    }

    if ($existingPerson) {
      $peopleModel->clearPeopleCache($oldProfileData['id']);
    } else {
      $peopleModel->clearPeopleCache();
    }

    // Ban/unban user
    if (isset($requestData['active'])) {
      $users = auth()->getProvider();
      $user = $users->findById($userId);
      if ($requestData['active']) {
        $user->unban();
      } else {
        $user->ban();
      }
    }

    $mappedData = [];
    foreach (PeopleModel::BASE_FIELDS as $key) {
      if (isset($requestData[$key])) {
        $mappedData[$key] = $requestData[$key];

        if (is_string($mappedData[$key])) {
          $mappedData[$key] = strip_tags($mappedData[$key]);
        }
      }
    }
    $data = $mappedData;

    $data['user_id'] = $userId;

    $peopleModel->db->transStart();

    $isUpdate = false;
    if ($existingPerson) {
      $isUpdate = true;
      $peopleModel->update($existingPerson['id'], $data);
    } else {
      $peopleModel->insert($data);
    }

    $record = $peopleModel->where('user_id', $userId)->first();
    if (!empty($record)) {
      $this->saveCustomFieldsProfileData($requestData, $record['id'], $record);
    }

    $peopleModel->db->transComplete();

    $newProfileData = $this->getProfileData($userId);

    if ((php_sapi_name() === 'cli' || auth()->user()) && $existingPerson) {
      $this->addAuditRecord($oldProfileData, $newProfileData, $userId, $sync, $noSync);
    }

    $peopleModel->indexInSearchIndex($newProfileData['id'], $newProfileData, $isUpdate);

    $message = $existingPerson ? 'Profile updated successfully.' : 'Profile created successfully.';

    return [$this->db->transStatus(), $message];
  }

  /**
   * Saves custom fields profile data to the database.
   *
   * @param array  $requestData      The data containing custom fields to be saved.
   * @param int    $userId           The ID of the user to whom the custom fields belong.
   * @param array  $personBasicData  Basic data of the person (optional).
   *
   * @return bool                    Returns true on success or false on failure.
   */
  private function saveCustomFieldsProfileData($requestData, $userId, $personBasicData) {
    $this->geoCodeUser($requestData);

    $peopleModel = new PeopleModel();
    $dataKeys = array_keys($requestData);

    if (empty($dataKeys)) {
      return false;
    }

    $db = \Config\Database::connect();
    $builder = $db->table('custom_fields');

    $customFieldsToProcess = $builder
      ->select('
        custom_fields.*
      ')
      ->where('parent_field_id IS NULL')
      ->whereIn('machine_name', $dataKeys)
      ->get()
      ->getResultArray();

    $customFieldsData = [];
    foreach ($customFieldsToProcess as $customFieldToProcess) {
      $value = $requestData[$customFieldToProcess['machine_name']];
      $fieldMetadata = json_decode($customFieldToProcess['metadata'], true);

      $fieldData = [
        'custom_field_id' => $customFieldToProcess['id'],
        'model_id' => $userId,
      ];

      if (isset($fieldMetadata['editableOnlyByAdmins']) && $fieldMetadata['editableOnlyByAdmins'] === true && php_sapi_name() !== 'cli' && (!auth()->user() || auth()->user()->can('admin.access') === false)) {
        continue;
      }

      switch ($customFieldToProcess['input_type']) {
        case 'tags':
        case 'tags_range':
          if ($customFieldToProcess['input_type'] === 'tags_range') {
            if (is_array($value)) {
              $value = array_map(function ($v) {
                if (isset($v['tags']) && is_array($v['tags']) === false) {
                  $v['tags'] = [$v['tags']];
                }

                if (isset($v['from']) && is_string($v['from'])) {
                  $v['from'] = strtotime($v['from']);
                }

                if (isset($v['to']) && is_string($v['to'])) {
                  $v['to'] = strtotime($v['to']);
                }

                return $v;
              }, $value);
            }
          }

          if ($customFieldToProcess['input_type'] === 'tags') {
            if (is_array($value)) {
              $value = array_map(function ($v) {
                $v = trim($v ?? '');

                return $v;
              }, $value);
            } else {
              $value = [$value];
            }
          }

          $fieldData['value_json'] = json_encode($value);
          $fieldData['value'] = '';

          break;
        case 'multi':
          if (isset($fieldMetadata['childFields']) && count($fieldMetadata['childFields']) > 0) {
            $childFieldsData = [];
            $childFieldsIds = array_map(fn($childField) => $childField['id'], $fieldMetadata['childFields']);
            $customFieldsModel = model('CustomFieldModel');
            $customFields = $customFieldsModel
              ->select('id, machine_name, input_type')
              ->whereIn('id', $childFieldsIds)
              ->findAll();

            $db
              ->table('custom_field_data')
              ->whereIn('custom_field_id', $childFieldsIds)
              ->where('model_id', $userId)
              ->delete();

            if (is_array($value) === false) {
              continue 2;
            }

            foreach ($value as $index => $itemData) {
              foreach ($itemData as $machineName => $itemDataValue) {
                $itemField = current(array_filter($customFields, fn($customFieldsItem) => $customFieldsItem['machine_name'] === $machineName));

                if (!isset($itemField['id'])) {
                  continue;
                }

                if ($itemField['input_type'] === 'tags') {
                  $childFieldsData[] = [
                    'custom_field_id' => $itemField['id'],
                    'model_id' => $userId,
                    'parent_field_value_index' => $index,
                    'value' => '',
                    'value_json' => json_encode($itemDataValue),
                  ];
                } else {
                  $childFieldsData[] = [
                    'custom_field_id' => $itemField['id'],
                    'model_id' => $userId,
                    'parent_field_value_index' => $index,
                    'value' => $itemDataValue,
                    'value_json' => '[]',
                  ];
                }
              }
            }

            if (!empty($childFieldsData)) {
              $builder = $db->table('custom_field_data');
              $result = $builder
                ->onConstraint(['custom_field_id', 'model_id', 'parent_field_value_index'])
                ->upsertBatch($childFieldsData);
            }

            $fieldData['value'] = '';
            $fieldData['value_json'] = '[]';
          }

          break;
        default:
          $fieldData['value'] = strip_tags($value);
          $fieldData['value_json'] = '[]';

          break;
      }

      if (!($personBasicData && $personBasicData['image_url']) &&
        (isset($fieldMetadata['isImportProfileImageLink']) && $fieldMetadata['isImportProfileImageLink'] === true)
      ) {
        $dirPath = ROOTPATH . 'writable/uploads/profile_images';
        if ($value && $profileImageName = $this->downloadRemoteImage($value, $dirPath)) {
          $peopleModel->update($personBasicData['id'], [
            'image_url' => $profileImageName,
          ]);
        }
      }

      $customFieldsData[] = $fieldData;
    }

    if (!empty($customFieldsData)) {
      $builder = $db->table('custom_field_data');
      $result = $builder
        ->onConstraint(['custom_field_id', 'model_id', 'parent_field_value_index'])
        ->upsertBatch($customFieldsData);
    } else {
      $result = true;
    }

    return $result;
  }

  /**
   * Downloads a remote image and saves it to the local directory after resizing.
   *
   * @param string $remoteURL      The URL of the remote image.
   * @param string $localDirectory The directory where the image will be saved locally.
   *
   * @return string|false          The filename of the saved image on success, or false on failure.
   */
  private function downloadRemoteImage($remoteURL, $localDirectory) {
    helper('filesystem');

    try {
      $imageData = file_get_contents($remoteURL);

      if ($imageData !== false) {
        $remoteURLParts = explode('?', $remoteURL);
        $extension = pathinfo($remoteURLParts[0], PATHINFO_EXTENSION);
        $randomFileName = md5(uniqid('', true)) . '.' . $extension;
        $tempFilePath = sys_get_temp_dir() . '/' . $randomFileName;

        if (!write_file($tempFilePath, $imageData)) {
          throw new \Exception("Couldn't save image {$tempFilePath}.");
        }

        $mime = mime_content_type($tempFilePath);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];

        if (!in_array($mime, $allowedMimeTypes)) {
          unlink($tempFilePath);
          throw new \Exception("Wrong mime type {$tempFilePath}.");
        }

        $localFilePath = $localDirectory . '/' . $randomFileName;
        copy($tempFilePath, $localFilePath);
        unlink($tempFilePath);

        $image = new ImageResize($localFilePath);
        $image->resizeToWidth(300);
        $image->save($localFilePath);

        return $randomFileName;
      } else {
        throw new \Exception('Remote image contents empty.');
      }
    } catch (\Exception $e) {
      log_message('error', "Couldn't fetch image {$remoteURL}.");
      return false;
    }
  }

  private function getChildCustomFields() {
    $db = \Config\Database::connect();
    $builder = $db->table('custom_fields cf');
    $childFields = $builder
      ->select('cf.machine_name')
      ->where('model_name', 'People')
      ->where('parent_field_id IS NOT NULL')
      ->get()
      ->getResultArray();

    $childFields = array_map(function ($field) {
      return $field['machine_name'];
    }, $childFields);

    return $childFields;
  }

  /**
   * Add an audit record for a user profile change.
   *
   * This function creates an audit record if there are changes in the profile data of an existing person.
   * It compares the old profile data with the new data and logs the differences, excluding certain fields.
   * If changes are found, they are recorded in the 'audit' table. Additionally, if any changes require 
   * review (as defined by REVIEW_TRIGGER_FIELDS), an email notification is sent to the designated reviewers.
   *
   * @param array $oldProfileData The previous profile data before the update.
   * @param int $userId The ID of the user whose profile is being updated.
   * @param bool $sync Whether to synchronize the user data with the remote service.
   * @param bool $noSync Whether to skip synchronization with the remote service.
   * @return void
   */
  private function addAuditRecord($oldProfileData, $newProfileData, $userId, bool $sync = false, bool $noSync = false) : void {
    $keysToSkip = array_merge(self::AUDIT_SKIP_FIELDS, $this->getChildCustomFields());
    $newValues = ArrayDiffMultidimensional::compare($newProfileData, $oldProfileData, false);
    $newValues = array_diff_key($newValues, array_flip($keysToSkip));
    $oldValues = ArrayDiffMultidimensional::compare($oldProfileData, $newProfileData, false);
    $oldValues = array_diff_key($oldValues, array_flip($keysToSkip));
    $review = 'not_required';
    $countedNewValues = count($newValues);

    // Check if the user is editing their own profile
    // and if the changes require review
    // and if the user is not an admin
    // and if the script is not running in CLI mode
    // to determine if the changes require review
    $reviewNeeded = (
      (
        php_sapi_name() !== 'cli' &&
        (
          auth()->user()->id === $userId &&
          auth()->user()->can('admin.access') === false
        )
      ) &&
      array_intersect(array_keys($newValues), self::REVIEW_TRIGGER_FIELDS)
    );

    if ($reviewNeeded) {
      $review = 'required';
    }

    // If there are changes, create an audit record
    if ($countedNewValues > 0) {
      if (php_sapi_name() !== 'cli') {
        $changedUserId = auth()->id();
      } else {
        $users = auth()->getProvider();
        $systemUser = $users->findByCredentials(['email' => 'system_user@example.com']);
        $changedUserId = $systemUser->id;
      }

      $db = \Config\Database::connect();
      $builder = $db->table('audit');
      $builder->insert([
        'audited_id' => $userId,
        'model_name' => 'People',
        'changed_user_id' => $changedUserId,
        'changes' => json_encode([
          'new' => $newValues,
          'old' => $oldValues,
        ]),
        'review' => $review,
      ]);

      if ($reviewNeeded) {
        $review = 'required';

        $email = \Config\Services::email();
        $name = $newProfileData['first_name'] . ' ' . $newProfileData['last_name'];
        $userEmail = '';
        if ($newProfileData['email']) {
          $userEmail = ' ' . $newProfileData['email'];
        }
        $insertedId = $db->insertID();
        $dataAuditLink = site_url("admin/profile_data_audit/{$insertedId}");
        $email->setTo(SystemSettingsWrapper::getInstance()->getSettingByKey('DataAuditReviewAdminEmails')['value']);
        $email->setSubject("Profile edit made by {$name}{$userEmail}");
        $email->setMessage("
          Action required to accept profile changes made by {$name}{$userEmail}.<br><br>" . "
          Click to process it: <a href=\"{$dataAuditLink}\">{$dataAuditLink}</a>.
        ");

        $email->send();
      }
    }

    // Synchronize user data with the remote service when saved by an admin
    if (
      $noSync === false &&
      (
        $sync === true ||
        ($countedNewValues > 0 &&
        auth()->user()->can('admin.access') === true)
      )
    ) {
      $dataAuditModel = new DataAuditModel();
      $dataAuditModel->syncUserData($newProfileData);
    }
  }

  /**
   * Geocodes the user based on their current or home city.
   *
   * @param array $requestData The user data containing the current or home city.
   * @return bool
   */
  private function geoCodeUser(&$requestData) {
    try {
      $currentCity = $requestData['current_city'] ?? '';
      $homeCity = $requestData['home_city'] ?? '';

      // Check if either city is non-empty
      if ($currentCity || $homeCity) {
        // Get the API key from the environment variable
        $geoApiKey = $_ENV['geocode_mapbox_api.key'] ?? '';
        if (empty($geoApiKey)) {
          throw new \Exception('Geo API key is missing in environment variables.');
        }

        $geoQueryArray = [
          $requestData['current_city'] ?? '',
          $requestData['current_state'] ?? '',
          $requestData['current_country'] ?? '',
        ];

        // Fallback to home location if no current location details are available
        if (count($geoQueryArray) === 0) {
          $geoQueryArray = [
            $requestData['home_city'] ?? '',
            $requestData['home_state'] ?? '',
            $requestData['home_country'] ?? '',
          ];
        }

        $geoQueryArray = array_filter($geoQueryArray);

        // Proceed if geoQueryArray is not empty
        if (!empty($geoQueryArray)) {
          $geoQuery = urlencode(join(',', $geoQueryArray));

          // Fetch geocode data and handle potential issues with the API request
          $geoApiResponse = file_get_contents("https://api.mapbox.com/geocoding/v5/mapbox.places/{$geoQuery}.json?access_token={$geoApiKey}");
          if ($geoApiResponse === false) {
            throw new \Exception('Failed to retrieve data from Geo API.');
          }

          $geoApiResponseData = json_decode($geoApiResponse, true);

          // Ensure the API response has the expected structure
          if (isset($geoApiResponseData['features']) && count($geoApiResponseData['features']) > 0) {
            $requestData['current_location_lon'] = strval($geoApiResponseData['features'][0]['center'][0]);
            $requestData['current_location_lat'] = strval($geoApiResponseData['features'][0]['center'][1]);
          }
        }
      }
    } catch (\Throwable $exception) {
      error_log($exception->getMessage());

      return false;
    }

    if (isset($requestData['current_location_lon']) && isset($requestData['current_location_lat'])) {
      return true;
    } else {
      return false;
    }
  }
}
