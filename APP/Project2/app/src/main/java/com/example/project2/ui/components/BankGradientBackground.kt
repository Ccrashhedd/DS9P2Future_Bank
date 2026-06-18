package com.example.project2.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxScope
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.blur
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.unit.dp
import com.example.project2.ui.theme.DarkBackground
import com.example.project2.ui.theme.DarkGradientMiddle
import com.example.project2.ui.theme.DarkGradientStart
import com.example.project2.ui.theme.Silver
import com.example.project2.ui.theme.SoftPurple

@Composable
fun BankGradientBackground(content: @Composable BoxScope.() -> Unit) {
    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.linearGradient(
                    colors = listOf(DarkGradientStart, DarkGradientMiddle, DarkBackground),
                    start = Offset.Zero,
                    end = Offset.Infinite
                )
            )
    ) {
        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    Brush.radialGradient(
                        colors = listOf(SoftPurple.copy(alpha = 0.22f), SoftPurple.copy(alpha = 0f)),
                        center = Offset(120f, 120f),
                        radius = 420f
                    )
                )
                .blur(18.dp)
        )
        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    Brush.radialGradient(
                        colors = listOf(Silver.copy(alpha = 0.10f), Silver.copy(alpha = 0f)),
                        center = Offset(900f, 1100f),
                        radius = 520f
                    ),
                    shape = CircleShape
                )
                .blur(22.dp)
        )
        content()
    }
}
