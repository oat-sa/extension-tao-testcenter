<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 Open Assessment Technologies SA
 */

namespace oat\taoTestCenter\scripts\tools;

use common_persistence_KeyValuePersistence;
use oat\oatbox\service\ServiceManagerAwareInterface;
use oat\oatbox\service\ServiceManagerAwareTrait;

class TmpKvTable implements ServiceManagerAwareInterface
{
    use ServiceManagerAwareTrait;

    const PREFIX = 'mapper_tt_';

    private $persistence;

    /**
     * @param $key
     * @param $value
     * @return bool
     * @throws \Exception
     */
    public function add($key, $value)
    {
        return $this->getPersistence()->set($this->computeKey($key), $value);
    }

    /**
     * @param $key
     * @return string
     * @throws \Exception
     */
    public function lookup($key)
    {
        return $this->getPersistence()->get($this->computeKey($key));
    }

    private function computeKey($key)
    {
        return static::PREFIX . $key;
    }

    /**
     * @throws \Exception
     */
    private function getPersistence()
    {
        if (is_null($this->persistence)) {
            $persistenceId = 'mapOfflineToOnlineResultIds';
            $persistence = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);

            if (!$persistence instanceof common_persistence_KeyValuePersistence) {
                throw new \Exception('Only common_persistence_KeyValuePersistence supported');
            }

            $this->persistence = $persistence;
        }

        return $this->persistence;
    }
}