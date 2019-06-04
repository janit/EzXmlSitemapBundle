<?php

namespace Janit\EzXmlSitemapBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

class DefaultController extends Controller
{

    public function simpleSitemapAction()
    {
        set_time_limit(-1);

        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge( 3600 );
        $response->headers->set('Content-Type', 'text/xml');

        $rootLocationId = $this->getConfigResolver()->getParameter( 'content.tree_root.location_id' );

        $repository = $this->container->get( 'ezpublish.api.repository' );

        $rootLocation = $repository->getLocationService()->loadLocation($rootLocationId);

        $criteria = [
            new Criterion\Subtree($rootLocation->pathString),
            new Criterion\Visibility( Criterion\Visibility::VISIBLE )
        ];

        $query = new LocationQuery(['filter' => new Criterion\LogicalAnd( $criteria )]);

        $query->limit = 1000000;

        $searchService = $repository->getSearchService();

        $searchResults = $searchService->findLocations($query);

        return $this->render(
            'JanitEzXmlSitemapBundle::sitemap.xml.twig',
            array(
                'searchHits' => $searchResults->searchHits,
            ),
            $response
        );

    }

}
