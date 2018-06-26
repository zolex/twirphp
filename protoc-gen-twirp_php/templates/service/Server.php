<?php
# Generated by the protocol buffer compiler (protoc-gen-twirp_php {{ .Version }}).  DO NOT EDIT!
# source: {{ .File.Name }}

namespace {{ .File | phpNamespace }};

use Google\Protobuf\Internal\GPBDecodeException;
use Http\Message\MessageFactory;
use Http\Message\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twirp\BaseServerHooks;
use Twirp\Context;
use Twirp\ErrorCode;
use Twirp\RequestHandler;
use Twirp\ServerHook;

/**
 * @see {{ .Service | phpServiceName .File }}
 *
 * Generated from protobuf service <code>{{ .Service | protoFullName .File }}</code>
 */
final class {{ .Service | phpServiceName .File }}Server extends TwirpServer implements RequestHandler
{
    const PATH_PREFIX = '/twirp/{{ .Service | protoFullName .File }}/';

    /**
     * @var {{ .Service | phpServiceName .File }}
     */
    private $svc;

    /**
     * @var ServerHook
     */
    private $hook;

    /**
     * @param {{ .Service | phpServiceName .File }} $svc
     * @param ServerHooks|null    $hook
     * @param MessageFactory|null $messageFactory
     * @param StreamFactory|null  $streamFactory
     */
    public function __construct(
        {{ .Service | phpServiceName .File }} $svc,
        ServerHook $hook = null,
        MessageFactory $messageFactory = null,
        StreamFactory $streamFactory = null
    ) {
        parent::__construct($messageFactory, $streamFactory);

        if ($hook === null) {
            $hook = new BaseServerHooks();
        }

        $this->svc = $svc;
        $this->hook = $hook;
    }

    /**
     * Handle the request and return a response.
     *
     * @param ServerRequestInterface $req
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $req)
    {
        $ctx = $req->getAttributes();
        $ctx = Context::withPackageName($ctx, '{{ .File.Package }}');
        $ctx = Context::withServiceName($ctx, '{{ .Service.Name }}');

        try {
            $ctx = $this->hook->requestReceived($ctx);
        } catch (\Twirp\Error $e) {
            return $this->writeError($ctx, $e);
        } catch (\Twirp\Exception $e) {
            return $this->writeError($ctx, $e->getError());
        } catch (\Exception $e) {
            return $this->writeError($ctx, TwirpError::errorFromException($e));
        }

        if ($req->getMethod() !== 'POST') {
            $msg = sprintf('unsupported method "%s" (only POST is allowed)', $req->getMethod());

            return $this->writeError($ctx, $this->badRouteError($msg, $req->getMethod(), $req->getUri()->getPath()));
        }

        switch ($req->getUri()->getPath()) {
            {{- range $method := .Service.Method }}
            case '/twirp/{{ $method | protoFullName $.File $.Service }}':
                return $this->handle{{ $method.Name }}($ctx, $req);
            {{- end }}

            default:
                return $this->writeError($ctx, $this->noRouteError($req));
        }
    }
{{ range $method := .Service.Method }}
    {{- $inputType := $method.InputType | phpMessageName }}
    private function handle{{ $method.Name }}(array $ctx, ServerRequestInterface $req)
    {
        $header = $req->getHeaderLine('Content-Type');
        $i = strpos($header, ';');

        if ($i === false) {
            $i = strlen($header);
        }

        $respHeaders = [];
        $ctx[Context::RESPONSE_HEADER] = &$respHeaders;

        switch (trim(strtolower(substr($header, 0, $i)))) {
            case 'application/json':
                $resp = $this->handle{{ $method.Name }}Json($ctx, $req);
                break;

            case 'application/protobuf':
                $resp = $this->handle{{ $method.Name }}Protobuf($ctx, $req);
                break;

            default:
                $msg = sprintf('unexpected Content-Type: "%s"', $req->getHeaderLine('Content-Type'));

                return $this->writeError($ctx, $this->badRouteError($msg, $req->getMethod(), $req->getUri()->getPath()));
        }

        foreach ($respHeaders as $key => $value) {
            $resp = $resp->withHeader($key, $value);
        }

        return $resp;
    }

    private function handle{{ $method.Name }}Json(array $ctx, ServerRequestInterface $req)
    {
        $ctx = Context::withMethodName($ctx, '{{ $method.Name }}');

        try {
            $ctx = $this->hook->requestRouted($ctx);

            $in = new {{ $inputType }}();
            $in->mergeFromJsonString((string)$req->getBody());

            $out = $this->svc->{{ $method.Name }}($ctx, $in);

            if ($out === null) {
                return $this->writeError($ctx, TwirpError::newError(ErrorCode::Internal, 'received a null response while calling {{ $method.Name }}. null responses are not supported'));
            }

            $ctx = $this->hook->responsePrepared($ctx);
        } catch (GPBDecodeException $e) {
            return $this->writeError($ctx, TwirpError::newError(ErrorCode::Internal, 'failed to parse request json'));
        } catch (\Twirp\Error $e) {
            return $this->writeError($ctx, $e);
        } catch (\Twirp\Exception $e) {
            return $this->writeError($ctx, $e->getError());
        } catch (\Exception $e) {
            return $this->writeError($ctx, TwirpError::errorFromException($e));
        }

        $data = $out->serializeToJsonString();

        $body = $this->streamFactory->createStream($data);

        $resp = $this->messageFactory
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        $this->callResponseSent($ctx);

        return $resp;
    }

    private function handle{{ $method.Name }}Protobuf(array $ctx, ServerRequestInterface $req)
    {
        $ctx = Context::withMethodName($ctx, '{{ $method.Name }}');

        try {
            $ctx = $this->hook->requestRouted($ctx);

            $in = new {{ $inputType }}();
            $in->mergeFromString((string)$req->getBody());

            $out = $this->svc->{{ $method.Name }}($ctx, $in);

            if ($out === null) {
                return $this->writeError($ctx, TwirpError::newError(ErrorCode::Internal, 'received a null response while calling {{ $method.Name }}. null responses are not supported'));
            }

            $ctx = $this->hook->responsePrepared($ctx);
        } catch (GPBDecodeException $e) {
            return $this->writeError($ctx, TwirpError::newError(ErrorCode::Internal, 'failed to parse request proto'));
        } catch (\Twirp\Error $e) {
            return $this->writeError($ctx, $e);
        } catch (\Twirp\Exception $e) {
            return $this->writeError($ctx, $e->getError());
        } catch (\Exception $e) {
            return $this->writeError($ctx, TwirpError::errorFromException($e));
        }

        $data = $out->serializeToString();

        $body = $this->streamFactory->createStream($data);

        $resp = $this->messageFactory
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/protobuf')
            ->withBody($body);

        $this->callResponseSent($ctx);

        return $resp;
    }
{{- end }}

    /**
     * Writes Twirp errors in the response and triggers hooks.
     *
     * @param array        $ctx
     * @param \Twirp\Error $e
     *
     * @return ResponseInterface
     */
    protected function writeError(array $ctx, \Twirp\Error $e)
    {
        $statusCode = ErrorCode::serverHTTPStatusFromErrorCode($e->code());
        $ctx = Context::withStatusCode($ctx, $statusCode);

        try {
            $ctx = $this->hook->error($ctx, $e);
        } catch (\Exception $e) {
            // We have three options here. We could log the error, call the Error
            // hook, or just silently ignore the error.
            //
            // Logging is unacceptable because we don't have a user-controlled
            // logger; writing out to stderr without permission is too rude.
            //
            // Calling the Error hook would confuse users: it would mean the Error
            // hook got called twice for one request, which is likely to lead to
            // duplicated log messages and metrics, no matter how well we document
            // the behavior.
            //
            // Silently ignoring the error is our least-bad option. It's highly
            // likely that the connection is broken and the original 'err' says
            // so anyway.
        }

        $this->callResponseSent($ctx);

        return parent::writeError($ctx, $e);
    }

    /**
     * Triggers response sent hook.
     *
     * @param array $ctx
     */
    private function callResponseSent(array $ctx)
    {
        try {
            $this->hook->responseSent($ctx);
        } catch (\Exception $e) {
            // We have three options here. We could log the error, call the Error
            // hook, or just silently ignore the error.
            //
            // Logging is unacceptable because we don't have a user-controlled
            // logger; writing out to stderr without permission is too rude.
            //
            // Calling the Error hook could confuse users: this hook is triggered
            // by the error hook itself, which is likely to lead to
            // duplicated log messages and metrics, no matter how well we document
            // the behavior.
            //
            // Silently ignoring the error is our least-bad option. It's highly
            // likely that the connection is broken and the original 'err' says
            // so anyway.
        }
    }
}
