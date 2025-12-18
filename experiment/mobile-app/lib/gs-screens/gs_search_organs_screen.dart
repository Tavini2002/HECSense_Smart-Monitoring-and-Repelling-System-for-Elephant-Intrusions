import 'package:flutter/material.dart';
import 'gs_dashboard_insights_screen.dart';

class OrganSearchView extends StatelessWidget {
  const OrganSearchView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: _SearchAppBar(onBack: () => Navigator.pop(context)),
      body: const SafeArea(
        child: Padding(
          padding: EdgeInsets.all(20),
          child: _SearchBody(),
        ),
      ),
    );
  }
}

/* -------------------- AppBar -------------------- */

class _SearchAppBar extends StatelessWidget implements PreferredSizeWidget {
  final VoidCallback onBack;

  const _SearchAppBar({required this.onBack});

  @override
  Widget build(BuildContext context) {
    return AppBar(
      elevation: 0,
      backgroundColor: Colors.white,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back, color: Colors.black),
        onPressed: onBack,
      ),
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);
}

/* -------------------- Body -------------------- */

class _SearchBody extends StatelessWidget {
  const _SearchBody();

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: const [
        _SearchHeader(),
        SizedBox(height: 40),
        Expanded(child: _SearchIllustration()),
        _NextActionButton(),
      ],
    );
  }
}

/* -------------------- Header -------------------- */

class _SearchHeader extends StatelessWidget {
  const _SearchHeader();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: const [
        Text(
          'Search the Organs',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 28,
            fontWeight: FontWeight.bold,
            color: Colors.black,
          ),
        ),
        SizedBox(height: 20),
        Text(
          'Explore available organs based on type, blood group, and other medical criteria. '
              'Our intuitive search helps you find exactly what you need.',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 16,
            color: Colors.black54,
          ),
        ),
      ],
    );
  }
}

/* -------------------- Image -------------------- */

class _SearchIllustration extends StatelessWidget {
  const _SearchIllustration();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Image.asset(
        'assets/images/search_icon.png',
        height: 350,
        width: 350,
      ),
    );
  }
}

/* -------------------- Button -------------------- */

class _NextActionButton extends StatelessWidget {
  const _NextActionButton();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 20),
      child: SizedBox(
        width: double.infinity,
        child: ElevatedButton(
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => const OrganSearchView(),
              ),
            );
          },
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.orange,
            padding: const EdgeInsets.symmetric(vertical: 15),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
          child: const Text(
            'Next',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
        ),
      ),
    );
  }
}
