<?php

/*
 * Copyright (C) 2016 andares.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Helper\Format\Http;

/**
 * Description of Constant
 *
 * @author andares
 */
class Constant {
    const METHOD_GET     = 'get';
    const METHOD_HEAD    = 'head';
    const METHOD_OPTIONS = 'options';
    const METHOD_TRACE   = 'trace';
    const METHOD_POST    = 'post';
    const METHOD_PUT     = 'put';
    const METHOD_DELETE  = 'delete';
    const METHOD_PATCH   = 'patch';

    const REQUEST_ACCEPT              = 'Accept';
    const REQUEST_ACCEPT_CHARSET      = 'Accept-Charset';
    const REQUEST_ACCEPT_ENCODING     = 'Accept-Encoding';
    const REQUEST_ACCEPT_LANGUAGE     = 'Accept-Language';
    const REQUEST_ACCEPT_DATETIME     = 'Accept-Datetime';
    const REQUEST_AUTHORIZATION       = 'Authorization';
    const REQUEST_CACHE_CONTROL       = 'Cache-Control';
    const REQUEST_CONNECTION          = 'Connection';
    const REQUEST_COOKIE              = 'Cookie';
    const REQUEST_CONTENT_LENGTH      = 'Content-Length';
    const REQUEST_CONTENT_MD5         = 'Content-MD5';
    const REQUEST_CONTENT_TYPE        = 'Content-Type';
    const REQUEST_DATE                = 'Date';
    const REQUEST_EXPECT              = 'Expect';
    const REQUEST_FROM                = 'From';
    const REQUEST_HOST                = 'Host';
    const REQUEST_IF_MATCH            = 'If-Match';
    const REQUEST_IF_MODIFIED_SINCE   = 'If-Modified-Since';
    const REQUEST_IF_NONE_MATCH       = 'If-None-Match';
    const REQUEST_IF_RANGE            = 'If-Range';
    const REQUEST_IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    const REQUEST_MAX_FORWARDS        = 'Max-Forwards';
    const REQUEST_ORIGIN              = 'Origin';
    const REQUEST_PRAGMA              = 'Pragma';
    const REQUEST_PROXY_AUTHORIZATION = 'Proxy-Authorization';
    const REQUEST_RANGE               = 'Range';
    const REQUEST_REFERER             = 'Referer';
    const REQUEST_TE                  = 'TE';
    const REQUEST_UPGRADE             = 'Upgrade';
    const REQUEST_USER_AGENT          = 'User-Agent';
    const REQUEST_VIA                 = 'Via';
    const REQUEST_WARNING             = 'Warning';
    const REQUEST_X_REQUESTED_WITH    = 'X-Requested-With';
    const REQUEST_DNT                 = 'DNT';
    const REQUEST_X_FORWARDED_FOR     = 'X-Forwarded-For';
    const REQUEST_X_FORWARDED_PROTO   = 'X-Forwarded-Proto';

    const RESPONSE_ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    const RESPONSE_ACCEPT_RANGES               = 'Accept-Ranges';
    const RESPONSE_AGE                         = 'Age';
    const RESPONSE_ALLOW                       = 'Allow';
    const RESPONSE_CACHE_CONTROL               = 'Cache-Control';
    const RESPONSE_CONNECTION                  = 'Connection';
    const RESPONSE_CONTENT_ENCODING            = 'Content-Encoding';
    const RESPONSE_CONTENT_LANGUAGE            = 'Content-Language';
    const RESPONSE_CONTENT_LENGTH              = 'Content-Length';
    const RESPONSE_CONTENT_LOCATION            = 'Content-Location';
    const RESPONSE_CONTENT_MD5                 = 'Content-MD5';
    const RESPONSE_CONTENT_DISPOSITION         = 'Content-Disposition';
    const RESPONSE_CONTENT_RANGE               = 'Content-Range';
    const RESPONSE_CONTENT_TYPE                = 'Content-Type';
    const RESPONSE_DATE                        = 'Date';
    const RESPONSE_ETAG                        = 'ETag';
    const RESPONSE_EXPIRES                     = 'Expires';
    const RESPONSE_LAST_MODIFIED               = 'Last-Modified';
    const RESPONSE_LINK                        = 'Link';
    const RESPONSE_LOCATION                    = 'Location';
    const RESPONSE_P3P                         = 'P3P';
    const RESPONSE_PRAGMA                      = 'Pragma';
    const RESPONSE_PROXY_AUTHENTICATE          = 'Proxy-Authenticate';
    const RESPONSE_REFRESH                     = 'Refresh';
    const RESPONSE_RETRY_AFTER                 = 'Retry-After';
    const RESPONSE_SERVER                      = 'Server';
    const RESPONSE_SET_COOKIE                  = 'Set-Cookie';
    const RESPONSE_STATUS                      = 'Status';
    const RESPONSE_STRICT_TRANSPORT_SECURITY   = 'Strict-Transport-Security';
    const RESPONSE_TRAILER                     = 'Trailer';
    const RESPONSE_TRANSFER_ENCODING           = 'Transfer-Encoding';
    const RESPONSE_VARY                        = 'Vary';
    const RESPONSE_VIA                         = 'Via';
    const RESPONSE_WARNING                     = 'Warning';
    const RESPONSE_WWW_AUTHENTICATE            = 'WWW-Authenticate';

    const CODE_CONTINUE_TRANSACTION            = 100;
    const CODE_SWITCHING_PROTOCOLS             = 101;
    const CODE_OK                              = 200;
    const CODE_CREATED                         = 201;
    const CODE_ACCEPTED                        = 202;
    const CODE_NON_AUTHORITATIVE_INFORMATION   = 203;
    const CODE_NO_CONTENT                      = 204;
    const CODE_RESET_CONTENT                   = 205;
    const CODE_PARTIAL_CONTENT                 = 206;
    const CODE_MULTIPLE_CHOICES                = 300;
    const CODE_MOVED_PERMANENTLY               = 301;
    const CODE_FOUND                           = 302;
    const CODE_SEE_OTHER                       = 303;
    const CODE_NOT_MODIFIED                    = 304;
    const CODE_USE_PROXY                       = 305;
    const CODE_TEMPORARY_REDIRECT              = 307;
    const CODE_BAD_REQUEST                     = 400;
    const CODE_UNAUTHORIZED                    = 401;
    const CODE_PAYMENT_REQUIRED                = 402;
    const CODE_FORBIDDEN                       = 403;
    const CODE_NOT_FOUND                       = 404;
    const CODE_METHOD_NOT_ALLOWED              = 405;
    const CODE_NOT_ACCEPTABLE                  = 406;
    const CODE_PROXY_AUTHENTICATION_REQUIRED   = 407;
    const CODE_REQUEST_TIME_OUT                = 408;
    const CODE_CONFLICT                        = 409;
    const CODE_GONE                            = 410;
    const CODE_LENGTH_REQUIRED                 = 411;
    const CODE_PRECONDITION_FAILED             = 412;
    const CODE_REQUEST_ENTITY_TOO_LARGE        = 413;
    const CODE_REQUEST_URI_TOO_LARGE           = 414;
    const CODE_UNSUPPORTED_MEDIA_TYPE          = 415;
    const CODE_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const CODE_EXPECTATION_FAILED              = 417;
    const CODE_INTERNAL_SERVER_ERROR           = 500;
    const CODE_NOT_IMPLEMENTED                 = 501;
    const CODE_BAD_GATEWAY                     = 502;
    const CODE_SERVICE_UNAVAILABLE             = 503;
    const CODE_GATEWAY_TIME_OUT                = 504;
    const CODE_HTTP_VERSION_NOT_SUPPORTED      = 505;

    /**
     * Http Code Messages
     *
     * @var array
     */
    public static $code_messages = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version Not Supported',
    ];

}
