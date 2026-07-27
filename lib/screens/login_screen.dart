import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../config/theme.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _loading = false;

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    final auth = context.read<AuthProvider>();
    final ok = await auth.login(
      _usernameController.text.trim(),
      _passwordController.text,
    );
    if (!mounted) return;
    setState(() => _loading = false);
    if (ok) Navigator.pushReplacementNamed(context, '/home');
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      backgroundColor: AppTheme.ink,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 28),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'HELENA',
                    style: Theme.of(context).textTheme.displayLarge?.copyWith(
                      fontFamily: 'Inter', fontWeight: FontWeight.w800,
                      color: AppTheme.ignite,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  Text(
                    'QUEST',
                    style: Theme.of(context).textTheme.displayLarge?.copyWith(
                      fontFamily: 'Inter', fontWeight: FontWeight.w800,
                      color: AppTheme.paper,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Gincana gamificada',
                    style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                      color: AppTheme.chalk,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 40),

                  Card(
                    color: AppTheme.paper.withValues(alpha: 0.05),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          Icon(Icons.mail_outline, color: AppTheme.ignite, size: 28),
                          const SizedBox(height: 8),
                          Text(
                            'Use o usuario e senha fornecidos no envelope lacrado.',
                            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                              color: AppTheme.chalk,
                              fontFamily: 'Nunito',
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  TextFormField(
                    controller: _usernameController,
                    decoration: const InputDecoration(
                      labelText: 'Usuario',
                      prefixIcon: Icon(Icons.person_outline),
                      fillColor: Color(0xFF2A2D35),
                    ),
                    style: const TextStyle(fontFamily: 'Nunito', color: Colors.white),
                    validator: (v) => v == null || v.trim().isEmpty ? 'Informe o usuario' : null,
                  ),
                  const SizedBox(height: 16),

                  TextFormField(
                    controller: _passwordController,
                    obscureText: true,
                    decoration: const InputDecoration(
                      labelText: 'Senha',
                      prefixIcon: Icon(Icons.lock_outline),
                      fillColor: Color(0xFF2A2D35),
                    ),
                    style: const TextStyle(fontFamily: 'Nunito', color: Colors.white),
                    validator: (v) => v == null || v.isEmpty ? 'Informe a senha' : null,
                  ),
                  const SizedBox(height: 8),

                  if (auth.error != null)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: Text(auth.error!,
                        style: const TextStyle(color: AppTheme.error, fontSize: 13)),
                    ),

                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    height: 52,
                    child: ElevatedButton(
                      onPressed: _loading ? null : _submit,
                      child: _loading
                          ? const SizedBox(width: 22, height: 22,
                              child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                          : const Text('ENTRAR',
                              style: TextStyle(fontFamily: 'JetBrains Mono', fontSize: 15)),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
