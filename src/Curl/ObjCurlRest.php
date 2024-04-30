<?php
/**
 * Object-orientated cURL class for PHP
 *
 * @copyright 2016 David Zurborg
 * @author    David Zurborg <zurborg@cpan.org>
 * @link      https://github.com/zurborg/libcurl-objcurl-php
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Curl;

use Pirate\Hooray\Arr;
use Pirate\Hooray\Str;

/**
 * Sub-class of ObjCurl with RESTful helper methods
 *
 * Every helper method (create, read, update, delete and patch) accepts an array of URI and query parameters.
 * This replaces placeholder in the specified path with its value. All leftovers are interpreted as query parameters. For example:
 *
 * ```php
 * $curl->path('/item/:item_id.json');
 * $curl->read(['item_id' => 1234, 'sort' => 'name']);
 * ```
 *
 * This resolves to `GET /item/1234.json?sort=name`.
 */
class ObjCurlRest extends ObjCurl
{
    /**
     * Replace placeholders with values
     *
     * @param string[] $params Path parameters
     *
     * @return self
     */
    public function params(array $params): self
    {
        $path = $this->url['path'];
        Str::replace(
            $path,
            '/:(\w+)/',
            function ($match) use (&$params, $path) {
                $key = $match[1];
                Arr::assert($params, $match[1], "Path $path - key :$key not found");
                return urlencode(Arr::consume($params, $key));
            }
        );
        $this->queries($params);
        $this->url['path'] = $path;
        return $this;
    }

    /**
     * Create a resource
     *
     * Performs a POST request
     *
     * @param string[] $params URI and query parameters
     */
    public function create(array $params = []): ObjCurl\Response
    {
        $this->params($params);
        return parent::post();
    }

    /**
     * Read a resource
     *
     * Performs a GET request
     *
     * @param string[] $params URI and query parameters
     */
    public function read(array $params = []): ObjCurl\Response
    {
        $this->params($params);
        return parent::get();
    }

    /**
     * Replace a resource
     *
     * Performs a PUT request
     *
     * @param string[] $params URI and query parameters
     */
    public function update(array $params = []): ObjCurl\Response
    {
        $this->params($params);
        return parent::put();
    }

    /**
     * Delete a resource
     *
     * Performs a DELETE request
     *
     * @param string[] $params URI and query parameters
     */
    public function delete(array $params = []): ObjCurl\Response
    {
        $this->params($params);
        return parent::delete();
    }

    /**
     * Update a resource
     *
     * Performs a PATCH request
     *
     * @param string[] $params URI and query parameters
     */
    public function patch(array $params = []): ObjCurl\Response
    {
        $this->params($params);
        return parent::patch();
    }
}
