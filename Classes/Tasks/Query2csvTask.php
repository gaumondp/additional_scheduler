<?php

declare(strict_types=1);

namespace Sng\Additionalscheduler\Tasks;

/*
 * This file is part of the "additional_scheduler" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\Additionalscheduler\BaseEmailTask;
use Sng\Additionalscheduler\Manager\CsvExportManager;
use Sng\Additionalscheduler\Utils;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Query2csvTask extends BaseEmailTask
{
    /**
     * @var string
     */
    public $query;

    /**
     * @var string
     */
    public $delimiter;

    /**
     * @var string
     */
    public $enclosure;

    /**
     * @var string
     */
    public $escape;

    /**
     * @var bool
     */
    public $noHeader;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var int
     */
    public $noDatetimeFlag;

    /**
     * @var string
     */
    public $body;

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $this->query = preg_replace('#\r\n#', ' ', $this->query);

        $mailSubject = $this->subject ?: $this->getDefaultSubject('query2csv');

        // Construct the filename with date-time if necessary
        $baseFilename = str_replace('.csv', '', $this->filename);
        if ($this->noDatetimeFlag == 0) {
            $finalFilename = $baseFilename . date('-Y-m-d_Hi') . '.csv';
        } else {
            $finalFilename = $baseFilename . '.csv';
        }

        $path = GeneralUtility::makeInstance(CsvExportManager::class)
            ->setQuery($this->query)
            ->setDelimiter($this->delimiter)
            ->setEnclosure($this->enclosure)
            ->setEscape($this->escape)
            ->setNoHeader((bool)$this->noHeader)
            ->renderFile($finalFilename); // Pass the final filename here

        if (!empty($this->email)) {
            // Use the final filename for the email attachment
            Utils::sendEmail($this->email, $mailSubject, $this->body, 'plain', 'utf-8', [$finalFilename => $path]);
        }

        unlink($path);
        return true;
    }
}
