package com.example.project2.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import com.example.project2.ui.theme.PrimaryPurple
import com.example.project2.ui.theme.SilverDark
import com.example.project2.ui.theme.SoftPurple

@Composable
fun BankPrimaryButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    enabled: Boolean = true,
    loading: Boolean = false
) {
    val shape = RoundedCornerShape(999.dp)
    Box(
        modifier = modifier
            .fillMaxWidth()
            .height(52.dp)
            .clip(shape)
            .background(
                if (enabled) {
                    Brush.horizontalGradient(listOf(PrimaryPurple, SoftPurple))
                } else {
                    Brush.horizontalGradient(listOf(SilverDark.copy(alpha = 0.35f), SilverDark.copy(alpha = 0.22f)))
                }
            )
            .then(
                if (enabled && !loading) {
                    Modifier.clickable(
                        interactionSource = remember { MutableInteractionSource() },
                        indication = null,
                        onClick = onClick
                    )
                } else {
                    Modifier
                }
            ),
        contentAlignment = Alignment.Center
    ) {
        if (loading) {
            CircularProgressIndicator(
                modifier = Modifier.height(22.dp),
                strokeWidth = 2.dp,
                color = Color.White
            )
        } else {
            Text(text = text, color = Color.White, style = MaterialTheme.typography.titleMedium)
        }
    }
}
