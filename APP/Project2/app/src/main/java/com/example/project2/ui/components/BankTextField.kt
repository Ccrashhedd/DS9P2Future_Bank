package com.example.project2.ui.components

import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import com.example.project2.ui.theme.GlassSurface
import com.example.project2.ui.theme.PrimaryPurple
import com.example.project2.ui.theme.SilverDark
import com.example.project2.ui.theme.TextPrimary
import com.example.project2.ui.theme.TextSecondary

@Composable
fun BankTextField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier,
    leadingIcon: ImageVector? = null,
    singleLine: Boolean = true
) {
    OutlinedTextField(
        value = value,
        onValueChange = onValueChange,
        modifier = modifier.fillMaxWidth(),
        label = { Text(label) },
        leadingIcon = leadingIcon?.let { icon -> { Icon(icon, contentDescription = null) } },
        singleLine = singleLine,
        shape = MaterialTheme.shapes.large,
        colors = bankTextFieldColors()
    )
}

@Composable
internal fun bankTextFieldColors() = OutlinedTextFieldDefaults.colors(
    focusedTextColor = TextPrimary,
    unfocusedTextColor = TextPrimary,
    focusedContainerColor = GlassSurface,
    unfocusedContainerColor = GlassSurface,
    focusedBorderColor = PrimaryPurple,
    unfocusedBorderColor = SilverDark.copy(alpha = 0.6f),
    focusedLabelColor = TextSecondary,
    unfocusedLabelColor = TextSecondary,
    cursorColor = PrimaryPurple,
    focusedLeadingIconColor = TextSecondary,
    unfocusedLeadingIconColor = SilverDark,
    focusedTrailingIconColor = TextSecondary,
    unfocusedTrailingIconColor = SilverDark
)
