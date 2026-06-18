package com.example.project2.ui.theme

import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Shapes
import androidx.compose.material3.darkColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp

private val FutureBankColorScheme = darkColorScheme(
    primary = PrimaryPurple,
    onPrimary = Color.White,
    primaryContainer = DarkPurple,
    onPrimaryContainer = TextPrimary,
    secondary = Silver,
    onSecondary = DarkBackground,
    tertiary = SoftPurple,
    background = DarkBackground,
    onBackground = TextPrimary,
    surface = GlassSurface,
    onSurface = TextPrimary,
    surfaceVariant = GlassSurfaceStrong,
    onSurfaceVariant = TextSecondary,
    outline = GlassBorder,
    error = DangerRed,
    onError = Color.White
)

private val FutureBankShapes = Shapes(
    extraSmall = RoundedCornerShape(10.dp),
    small = RoundedCornerShape(14.dp),
    medium = RoundedCornerShape(20.dp),
    large = RoundedCornerShape(28.dp),
    extraLarge = RoundedCornerShape(34.dp)
)

@Composable
fun Project2Theme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = FutureBankColorScheme,
        typography = Typography,
        shapes = FutureBankShapes,
        content = content
    )
}
