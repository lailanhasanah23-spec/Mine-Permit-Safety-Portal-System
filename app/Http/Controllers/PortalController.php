<?php

namespace App\Http\Controllers;

use App\Support\Legacy\LegacyAuth;
use App\Support\Legacy\LegacyRepository;

class PortalController extends Controller
{
    public function home()
    {
        return view('portal.home', $this->buildPortalData());
    }

    public function forms()
    {
        return view('portal.forms', $this->buildPortalData());
    }

    public function index()
    {
        return $this->forms();
    }

    public function simper()
    {
        return view('portal.simper', $this->buildPortalData());
    }

    private function buildPortalData(): array
    {
        $isAdminAuthenticated = LegacyAuth::user() !== null;
        $categories = LegacyRepository::portalGetCategories();
        $formsByCategory = LegacyRepository::portalGetFormsByCategory($categories, $isAdminAuthenticated);
        $internalGroups = LegacyRepository::portalGetInternalGroups();
        $requiredDocs = LegacyRepository::portalGetRequiredDocs();
        $sapkonBuckets = LegacyRepository::portalGetSapkonBuckets();
        $policyDocuments = LegacyRepository::portalGetPublicPolicyDocuments();
        $flowDocuments = LegacyRepository::portalGetFlowDocuments();

        $internalGroupMap = [];
        foreach ($internalGroups as $row) {
            $groupName = (string) $row['group_name'];
            if (! isset($internalGroupMap[$groupName])) {
                $internalGroupMap[$groupName] = [];
            }
            $internalGroupMap[$groupName][] = $row;
        }

        $requiredDocsMap = [
            'simper' => [],
            'mine_permit' => [],
        ];

        foreach ($requiredDocs as $doc) {
            $scope = (string) $doc['doc_scope'];
            if (! isset($requiredDocsMap[$scope])) {
                $requiredDocsMap[$scope] = [];
            }
            $requiredDocsMap[$scope][] = $doc;
        }

        return [
            'categories' => $categories,
            'formsByCategory' => $formsByCategory,
            'internalGroupMap' => $internalGroupMap,
            'requiredDocsMap' => $requiredDocsMap,
            'policyDocuments' => $policyDocuments,
            'flowDocuments' => $flowDocuments,
            'sapkonBuckets' => $sapkonBuckets,
            'isAdminAuthenticated' => $isAdminAuthenticated,
            'totalForms' => LegacyRepository::portalCountTotalForms($formsByCategory),
            'formsExpiringSoon' => LegacyRepository::countFormsExpiringSoon(7),
            'lastFormSyncAt' => LegacyRepository::getLastFormSyncAt(),
        ];
    }
}
