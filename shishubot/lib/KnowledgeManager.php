<?php

class KnowledgeManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function addKnowledge(string $entity, string $definition, int $userId): bool {
        try {
            // Always insert new knowledge, allowing duplicates for entities
            $stmt = $this->pdo->prepare("INSERT INTO knowledge_base (entity, definition, source_user_id) VALUES (:entity, :definition, :user_id)");
            return $stmt->execute([
                'entity' => $entity,
                'definition' => $definition,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getKnowledge(string $entity): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT kb.entity, kb.definition, u.username FROM knowledge_base kb JOIN users u ON kb.source_user_id = u.id WHERE kb.entity = :entity");
            $stmt->execute(['entity' => $entity]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                return null;
            }

            // Group definitions and count occurrences
            $definitions = [];
            foreach ($results as $row) {
                $definition = $row['definition'];
                if (!isset($definitions[$definition])) {
                    $definitions[$definition] = [
                        'count' => 0,
                        'source_users' => [],
                        'definition' => $definition
                    ];
                }
                $definitions[$definition]['count']++;
                $definitions[$definition]['source_users'][] = $row['username'];
            }

            // Sort by count (highest priority first)
            uasort($definitions, function($a, $b) {
                return $b['count'] <=> $a['count'];
            });

            // Return the top definition and all definitions for display
            return [
                'top_definition' => reset($definitions), // Get the first element (highest count)
                'all_definitions' => array_values($definitions) // Get all definitions as a simple array
            ];

        } catch (PDOException $e) {
            return null;
        }
    }

    public function addRelationship(string $entity1, string $relationshipType, string $entity2, int $userId): bool {
        try {
            // Get IDs of entities, create if they don't exist (with placeholder definition)
            $entity1Id = $this->getEntityId($entity1, $userId);
            $entity2Id = $this->getEntityId($entity2, $userId);

            if (!$entity1Id || !$entity2Id) {
                return false; // Failed to get/create entities
            }

            // Check if relationship already exists to prevent duplicates
            $stmt = $this->pdo->prepare("SELECT id FROM relationships WHERE entity1_id = :e1id AND relationship_type = :type AND entity2_id = :e2id");
            $stmt->execute([
                'e1id' => $entity1Id,
                'type' => $relationshipType,
                'e2id' => $entity2Id
            ]);
            if ($stmt->fetch()) {
                return true; // Relationship already exists
            }

            $stmt = $this->pdo->prepare("INSERT INTO relationships (entity1_id, relationship_type, entity2_id, source_user_id) VALUES (:entity1_id, :relationship_type, :entity2_id, :source_user_id)");
            return $stmt->execute([
                'entity1_id' => $entity1Id,
                'relationship_type' => $relationshipType,
                'entity2_id' => $entity2Id,
                'source_user_id' => $userId
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getRelationships(string $entity, string $relationshipType = ''): array {
        try {
            $entityId = $this->getEntityId($entity);
            if (!$entityId) {
                return [];
            }

            $sql = "SELECT kb2.entity AS related_entity, r.relationship_type, u.username FROM relationships r JOIN knowledge_base kb1 ON r.entity1_id = kb1.id JOIN knowledge_base kb2 ON r.entity2_id = kb2.id JOIN users u ON r.source_user_id = u.id WHERE kb1.id = :entity_id";
            $params = ['entity_id' => $entityId];

            if (!empty($relationshipType)) {
                $sql .= " AND r.relationship_type = :type";
                $params['type'] = $relationshipType;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getEntityProperties(string $entity): array {
        $properties = [];
        $visited = [];
        $this->collectPropertiesRecursive($entity, $properties, $visited);
        return $properties;
    }

    private function collectPropertiesRecursive(string $entity, array &$properties, array &$visited): void {
        if (in_array($entity, $visited)) {
            return; // Avoid infinite loops in circular relationships
        }
        $visited[] = $entity;

        $entityId = $this->getEntityId($entity);
        if (!$entityId) {
            return;
        }

        // Get direct properties
        $stmt = $this->pdo->prepare("SELECT kb2.entity AS property_name, r.relationship_type, u.username FROM relationships r JOIN knowledge_base kb1 ON r.entity1_id = kb1.id JOIN knowledge_base kb2 ON r.entity2_id = kb2.id JOIN users u ON r.source_user_id = u.id WHERE kb1.id = :entity_id AND r.relationship_type = 'has property'");
        $stmt->execute(['entity_id' => $entityId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $properties[] = $row;
        }

        // Get inherited properties through 'is a type of' relationships
        $stmt = $this->pdo->prepare("SELECT kb2.entity AS parent_entity FROM relationships r JOIN knowledge_base kb1 ON r.entity1_id = kb1.id JOIN knowledge_base kb2 ON r.entity2_id = kb2.id WHERE kb1.id = :entity_id AND r.relationship_type = 'is a type of'");
        $stmt->execute(['entity_id' => $entityId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->collectPropertiesRecursive($row['parent_entity'], $properties, $visited);
        }
    }

    private function getEntityId(string $entity, int $userId = null): ?int {
        $stmt = $this->pdo->prepare("SELECT id FROM knowledge_base WHERE entity = :entity");
        $stmt->execute(['entity' => $entity]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['id'];
        } elseif ($userId !== null) {
            // If entity doesn't exist and userId is provided, create it with a placeholder definition
            $this->addKnowledge($entity, "(No definition yet)", $userId);
            return $this->pdo->lastInsertId();
        }
        return null;
    }

    public function addDeferredQuestion(int $userId, string $questionText, ?string $relatedEntity = null): bool {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO deferred_questions (user_id, question_text, related_entity) VALUES (:user_id, :question_text, :related_entity)");
            return $stmt->execute([
                'user_id' => $userId,
                'question_text' => $questionText,
                'related_entity' => $relatedEntity
            ]);
        } catch (PDOException $e) {
            error_log("Error adding deferred question: " . $e->getMessage());
            return false;
        }
    }

    public function getPendingDeferredQuestionsByEntity(string $entity): array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM deferred_questions WHERE related_entity = :entity AND status = 'pending'");
            $stmt->execute(['entity' => $entity]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting pending deferred questions: " . $e->getMessage());
            return [];
        }
    }

    public function markDeferredQuestionAsResolved(int $questionId): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE deferred_questions SET status = 'resolved', resolved_at = NOW() WHERE id = :id");
            return $stmt->execute(['id' => $questionId]);
        } catch (PDOException $e) {
            error_log("Error marking deferred question as resolved: " . $e->getMessage());
            return false;
        }
    }
}

?>