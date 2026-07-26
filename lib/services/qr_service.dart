import 'dart:async';

class QrService {
  StreamController<String>? _controller;

  Stream<String> get stream {
    _controller ??= StreamController<String>.broadcast();
    return _controller!.stream;
  }

  void emitCode(String code) {
    _controller?.add(code);
  }

  void dispose() {
    _controller?.close();
    _controller = null;
  }
}
