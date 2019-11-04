<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;

/**
 * URLAlias service.
 *
 * @example Examples/urlalias.php
 */
interface URLAliasService
{
    /**
     * Create a user chosen $alias pointing to $location in $languageCode.
     *
     * This method runs URL filters and transformers before storing them.
     * Hence the path returned in the URLAlias Value may differ from the given.
     * $alwaysAvailable makes the alias available in all languages.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $path
     * @param string $languageCode the languageCode for which this alias is valid
     * @param bool $forwarding if true a redirect is performed
     * @param bool $alwaysAvailable
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create url alias
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path already exists for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function createUrlAlias(Location $location, string $path, string $languageCode, bool $forwarding = false, bool $alwaysAvailable = false): URLAlias;

    /**
     * Create a user chosen $alias pointing to a resource in $languageCode.
     *
     * This method does not handle location resources - if a user enters a location target
     * the createCustomUrlAlias method has to be used.
     * This method runs URL filters and and transformers before storing them.
     * Hence the path returned in the URLAlias Value may differ from the given.
     *
     * $alwaysAvailable makes the alias available in all languages.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create global
     *          url alias
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path already exists for the given
     *         language or if resource is not valid
     *
     * @param string $resource
     * @param string $path
     * @param string $languageCode
     * @param bool $forwarding
     * @param bool $alwaysAvailable
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function createGlobalUrlAlias(string $resource, string $path, string $languageCode, bool $forwarding = false, bool $alwaysAvailable = false): URLAlias;

    /**
     * List of url aliases pointing to $location, sorted by language priority.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param bool $custom if true the user generated aliases are listed otherwise the autogenerated
     * @param string|null $languageCode filters those which are valid for the given language
     * @param bool|null $showAllTranslations
     * @param string[]|null $prioritizedLanguageList
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias[]
     */
    public function listLocationAliases(Location $location, bool $custom = true, ?string $languageCode = null, bool $showAllTranslations = null, array $prioritizedLanguageList = null): iterable;

    /**
     * List global aliases.
     *
     * @param string|null $languageCode filters those which are valid for the given language
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias[]
     */
    public function listGlobalAliases(?string $languageCode = null, int $offset = 0, int $limit = -1): iterable;

    /**
     * Removes urls aliases.
     *
     * This method does not remove autogenerated aliases for locations.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove url alias
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if alias list contains
     *         autogenerated alias
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias[] $aliasList
     */
    public function removeAliases(array $aliasList): void;

    /**
     * looks up the URLAlias for the given url.
     *
     * @param string $url
     * @param string|null $languageCode
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the path does not exist or is not valid for the given language
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the path exceeded maximum depth level
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function lookup(string $url, ?string $languageCode = null): URLAlias;

    /**
     * Returns the URL alias for the given location in the given language.
     *
     * If $languageCode is null the method returns the url alias in the most prioritized language.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if no url alias exist for the given language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string|null $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function reverseLookup(Location $location, ?string $languageCode = null): URLAlias;

    /**
     * Loads URL alias by given $id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param string $id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function load(string $id): URLAlias;

    /**
     * Refresh all system URL aliases for the given Location (and historize outdated if needed).
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     */
    public function refreshSystemUrlAliasesForLocation(Location $location): void;

    /**
     * Delete global, system or custom URL alias pointing to non-existent Locations.
     *
     * @return int Number of deleted URL aliases
     */
    public function deleteCorruptedUrlAliases(): int;
}
