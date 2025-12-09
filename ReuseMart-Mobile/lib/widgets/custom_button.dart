import 'package:flutter/material.dart';
import 'package:flutter_spinkit/flutter_spinkit.dart';
import '../constants/app_constants.dart';

class CustomButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final bool isLoading;
  final Color? backgroundColor;
  final Color? textColor;
  final IconData? icon;
  final bool isOutlined;
  final double? width;
  final double? height;

  const CustomButton({
    super.key,
    required this.text,
    this.onPressed,
    this.isLoading = false,
    this.backgroundColor,
    this.textColor,
    this.icon,
    this.isOutlined = false,
    this.width,
    this.height,
  });

  @override
  Widget build(BuildContext context) {
    final buttonColor = backgroundColor ?? AppConstants.primaryColor;
    final contentColor = textColor ?? Colors.white;

    Widget buttonChild = Row(
      mainAxisAlignment: MainAxisAlignment.center,
      mainAxisSize: MainAxisSize.min,
      children: [
        if (isLoading) ...[
          SpinKitThreeBounce(
            color: contentColor,
            size: 20.0,
          ),
          const SizedBox(width: AppConstants.paddingSmall),
          Text(
            'Mohon tunggu...',
            style: AppConstants.bodyStyle.copyWith(
              color: contentColor,
              fontWeight: FontWeight.w500,
            ),
          ),
        ] else ...[
          if (icon != null) ...[
            Icon(icon, color: contentColor),
            const SizedBox(width: AppConstants.paddingSmall),
          ],
          Text(
            text,
            style: AppConstants.bodyStyle.copyWith(
              color: contentColor,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ],
    );

    if (isOutlined) {
      return SizedBox(
        width: width,
        height: height ?? 50,
        child: OutlinedButton(
          onPressed: isLoading ? null : onPressed,
          style: OutlinedButton.styleFrom(
            side: BorderSide(color: buttonColor),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
            ),
            padding: const EdgeInsets.symmetric(
              horizontal: AppConstants.paddingLarge,
              vertical: AppConstants.paddingMedium,
            ),
          ),
          child: buttonChild,
        ),
      );
    }

    return SizedBox(
      width: width,
      height: height ?? 50,
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: buttonColor,
          foregroundColor: contentColor,
          disabledBackgroundColor: buttonColor.withOpacity(0.6),
          disabledForegroundColor: contentColor.withOpacity(0.6),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppConstants.paddingLarge,
            vertical: AppConstants.paddingMedium,
          ),
          elevation: 2,
        ),
        child: buttonChild,
      ),
    );
  }
}
